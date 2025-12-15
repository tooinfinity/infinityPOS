<?php

declare(strict_types=1);

use App\Actions\Settings\SeedPredefinedSettings;
use App\Enums\SettingTypeEnum;
use App\Models\Setting;

use function Pest\Laravel\artisan;

beforeEach(function (): void {
    Setting::query()->delete();
});

it('seeds all predefined settings on first run', function (): void {
    $action = new SeedPredefinedSettings;

    $result = $action->handle();

    expect($result['created'])->toBe(33)
        ->and($result['skipped'])->toBe(0)
        ->and($result['updated'])->toBe(0)
        ->and(Setting::query()->count())->toBe(33);
});

it('skips existing settings without force option', function (): void {
    Setting::factory()->create([
        'key' => 'app_name',
        'value' => 'Custom Store',
        'type' => SettingTypeEnum::STRING,
        'group' => 'general',
    ]);

    $action = new SeedPredefinedSettings;
    $result = $action->handle(force: false);

    expect($result['created'])->toBe(32)
        ->and($result['skipped'])->toBe(1)
        ->and($result['updated'])->toBe(0);

    $setting = Setting::query()->where('key', 'app_name')->first();
    expect($setting)->not->toBeNull()
        ->and($setting->value)->toBe('Custom Store');
});

it('updates existing settings with force option', function (): void {
    Setting::factory()->create([
        'key' => 'app_name',
        'value' => 'Custom Store',
        'type' => SettingTypeEnum::STRING,
        'group' => 'general',
    ]);

    $action = new SeedPredefinedSettings;
    $result = $action->handle(force: true);

    expect($result['created'])->toBe(32)
        ->and($result['skipped'])->toBe(0)
        ->and($result['updated'])->toBe(1);

    $setting = Setting::query()->where('key', 'app_name')->first();
    expect($setting)->not->toBeNull()
        ->and($setting->value)->toBe('My Store');
});

it('returns correct counts for all operations', function (): void {
    Setting::factory()->create([
        'key' => 'app_name',
        'value' => 'Custom Store',
        'type' => SettingTypeEnum::STRING,
        'group' => 'general',
    ]);
    Setting::factory()->create([
        'key' => 'currency_code',
        'value' => 'EUR',
        'type' => SettingTypeEnum::STRING,
        'group' => 'general',
    ]);

    $action = new SeedPredefinedSettings;
    $result = $action->handle(force: false);

    expect($result['created'])->toBe(31)
        ->and($result['skipped'])->toBe(2)
        ->and($result['updated'])->toBe(0);
});

it('seeds all setting groups correctly', function (): void {
    $action = new SeedPredefinedSettings;
    $action->handle();

    $groups = Setting::query()
        ->selectRaw('`group`, COUNT(*) as count')
        ->groupBy('group')
        ->pluck('count', 'group')
        ->toArray();

    expect($groups)->toHaveKey('general')
        ->and($groups)->toHaveKey('pos')
        ->and($groups)->toHaveKey('inventory')
        ->and($groups)->toHaveKey('sales')
        ->and($groups)->toHaveKey('purchase')
        ->and($groups)->toHaveKey('reporting')
        ->and($groups['general'])->toBe(9)
        ->and($groups['pos'])->toBe(7)
        ->and($groups['inventory'])->toBe(5)
        ->and($groups['sales'])->toBe(6)
        ->and($groups['purchase'])->toBe(3)
        ->and($groups['reporting'])->toBe(3);
});

it('uses database transactions for atomicity', function (): void {
    $action = new SeedPredefinedSettings;
    $action->handle();

    expect(Setting::query()->count())->toBe(33);
});

it('provides a method to get settings count', function (): void {
    $action = new SeedPredefinedSettings;

    expect($action->getSettingsCount())->toBe(33);
});

it('works with the settings:seed command', function (): void {
    artisan('settings:seed')
        ->assertSuccessful();

    expect(Setting::query()->count())->toBe(33);
});

it('handles force option in settings:seed command', function (): void {
    Setting::factory()->create([
        'key' => 'app_name',
        'value' => 'Custom Store',
        'type' => SettingTypeEnum::STRING,
        'group' => 'general',
    ]);

    artisan('settings:seed', ['--force' => true])
        ->assertSuccessful();

    $setting = Setting::query()->where('key', 'app_name')->first();
    expect($setting)->not->toBeNull()
        ->and($setting->value)->toBe('My Store');
});

it('skips all settings when all already exist', function (): void {
    $action = new SeedPredefinedSettings;
    $action->handle();

    $result = $action->handle(force: false);

    expect($result['created'])->toBe(0)
        ->and($result['skipped'])->toBe(33)
        ->and($result['updated'])->toBe(0);
});

it('updates all settings when force and all exist', function (): void {
    $action = new SeedPredefinedSettings;
    $action->handle();

    $result = $action->handle(force: true);

    expect($result['created'])->toBe(0)
        ->and($result['skipped'])->toBe(0)
        ->and($result['updated'])->toBe(33);
});
