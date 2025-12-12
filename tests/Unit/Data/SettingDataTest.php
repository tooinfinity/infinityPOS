<?php

declare(strict_types=1);

use App\Data\SettingData;
use App\Data\UserData;
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

    $data = SettingData::from(
        $setting->load(['updater'])
    );

    expect($data)
        ->toBeInstanceOf(SettingData::class)
        ->id->toBe($setting->id)
        ->key->toBe('site_name')
        ->value->toBe('Acme Inc.')
        ->type->toBe(SettingTypeEnum::STRING)
        ->group->toBe('general')
        ->description->toBe('The public site name')
        ->and($data->updater->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($updater->id)
        ->and($data->created_at)
        ->toBe($setting->created_at->toDateTimeString())
        ->and($data->updated_at)
        ->toBe($setting->updated_at->toDateTimeString());
});
