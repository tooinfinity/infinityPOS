<?php

declare(strict_types=1);

use App\Enums\SettingTypeEnum;
use App\Models\Setting;
use App\Queries\Settings\GetAllSettingsQuery;

it('returns all settings as key-value pairs', function (): void {
    Setting::factory()->create(['key' => 'app_name', 'value' => 'InfinityPOS']);
    Setting::factory()->create(['key' => 'app_version', 'value' => '1.0.0']);

    $query = new GetAllSettingsQuery();
    $result = $query->handle();

    expect($result)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and($result->get('app_name'))->toBe('InfinityPOS')
        ->and($result->get('app_version'))->toBe('1.0.0');
});

it('returns empty collection when no settings exist', function (): void {
    $query = new GetAllSettingsQuery();
    $result = $query->handle();

    expect($result)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and($result)->toBeEmpty();
});

it('returns settings grouped by group name', function (): void {
    Setting::factory()->create(['key' => 'app_name', 'value' => 'InfinityPOS', 'group' => 'general']);
    Setting::factory()->create(['key' => 'app_version', 'value' => '1.0.0', 'group' => 'general']);
    Setting::factory()->create(['key' => 'currency', 'value' => 'USD', 'group' => 'finance']);

    $query = new GetAllSettingsQuery();
    $result = $query->handleGrouped();

    expect($result)->toHaveCount(2)
        ->and($result->has('general'))->toBeTrue()
        ->and($result->has('finance'))->toBeTrue()
        ->and($result->get('general'))->toHaveCount(2)
        ->and($result->get('finance'))->toHaveCount(1);
});

it('returns Setting models in grouped results', function (): void {
    Setting::factory()->create(['key' => 'app_name', 'value' => 'InfinityPOS', 'group' => 'general']);

    $query = new GetAllSettingsQuery();
    $result = $query->handleGrouped();

    expect($result->get('general')->get('app_name'))->toBeInstanceOf(Setting::class);
});

it('preserves setting types in grouped results', function (): void {
    Setting::factory()->create([
        'key' => 'is_active',
        'value' => true,
        'type' => SettingTypeEnum::BOOLEAN,
        'group' => 'general',
    ]);

    $query = new GetAllSettingsQuery();
    $result = $query->handleGrouped();

    $setting = $result->get('general')->get('is_active');

    expect($setting->type)->toBe(SettingTypeEnum::BOOLEAN);
});
