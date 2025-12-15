<?php

declare(strict_types=1);

use App\Data\SettingData;
use App\Enums\SettingTypeEnum;
use App\Models\Setting;
use App\Models\User;

it('transforms a setting model into SettingData', function (): void {
    $updater = User::factory()->create();

    /** @var Setting $setting */
    $setting = Setting::factory()
        ->for($updater, 'updater')
        ->create([
            'key' => 'site_name',
            'value' => 'Acme Inc.',
            'type' => SettingTypeEnum::STRING->value,
            'group' => 'general',
            'description' => 'The public site name',
        ]);

    $data = SettingData::from($setting);

    expect($data)
        ->toBeInstanceOf(SettingData::class)
        ->key->toBe('site_name')
        ->value->toBe('Acme Inc.')
        ->type->toBe(SettingTypeEnum::STRING)
        ->group->toBe('general');
});

it('Setting Data from an array ', function (): void {
    $updater = User::factory()->create();

    /** @var Setting $setting */
    $setting = Setting::factory()
        ->for($updater, 'updater')
        ->create([
            'key' => 'site_name',
            'value' => 'Acme Inc.',
            'type' => SettingTypeEnum::STRING->value,
            'group' => 'general',
            'description' => 'The public site name',
        ]);

    $data = SettingData::fromArray($setting->toArray());

    expect($data)->toBeInstanceOf(SettingData::class);
});

it('Setting Data to array', function (): void {
    $data = new SettingData(
        key: 'site_name',
        value: 'Acme Inc.',
        type: SettingTypeEnum::STRING,
        group: 'general',
        is_public: false,
    );
    $data = $data->toArray();

    expect($data)
        ->toBe([
            'key' => 'site_name',
            'value' => 'Acme Inc.',
            'type' => 'string',
            'group' => 'general',
            'is_public' => false,
        ]);
});
