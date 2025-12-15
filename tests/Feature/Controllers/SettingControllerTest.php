<?php

declare(strict_types=1);

use App\Enums\SettingTypeEnum;
use App\Models\Setting;
use App\Models\User;

it('returns a list of settings', function (): void {
    $user = User::factory()->create();
    config(['inertia.testing.ensure_pages_exist' => false]);
    Setting::factory()->create(['key' => 'app_name', 'value' => 'InfinityPOS', 'is_public' => true, 'group' => 'general']);

    $response = $this->actingAs($user)
        ->get(route('setting.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/index')
            ->has('groupedSettings')
        );
});

it('can update settings in bulk', function (): void {
    $user = User::factory()->create();
    Setting::factory()->create([
        'key' => 'app_name',
        'value' => 'Old Name',
        'type' => SettingTypeEnum::STRING,
    ]);

    $response = $this->actingAs($user)
        ->put(route('setting.update'), [
            'settings' => [
                ['key' => 'app_name', 'value' => 'New Name'],
            ],
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('settings', [
        'key' => 'app_name',
        'value' => 'New Name',
    ]);
});
