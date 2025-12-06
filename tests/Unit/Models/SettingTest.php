<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create()->refresh();
    $setting = Setting::factory()->create(['updated_by' => $user->id])->refresh();

    expect(array_keys($setting->toArray()))
        ->toBe([
            'id',
            'key',
            'value',
            'type',
            'group',
            'description',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});

test('setting relationships', function (): void {
    $user = User::factory()->create()->refresh();
    $setting = Setting::factory()->create(['updated_by' => $user->id]);

    expect($setting->updater->id)->toBe($user->id);
});
