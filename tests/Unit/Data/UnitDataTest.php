<?php

declare(strict_types=1);

use App\Data\UnitData;
use App\Data\UserData;
use App\Models\Unit;
use App\Models\User;

it('transforms a unit model into UnitData', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    /** @var Unit $unit */
    $unit = Unit::factory()
        ->for($creator, 'creator')
        ->for($updater, 'updater')
        ->create([
            'name' => 'Kilogram',
            'short_name' => 'kg',
            'is_active' => true,
        ]);

    $data = UnitData::from(
        $unit->load(['creator', 'updater'])
    );

    expect($data)
        ->toBeInstanceOf(UnitData::class)
        ->id->toBe($unit->id)
        ->name->toBe('Kilogram')
        ->short_name->toBe('kg')
        ->is_active->toBeTrue()
        ->and($data->creator->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($creator->id)
        ->and($data->updater->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($updater->id)
        ->and($data->created_at)
        ->toBe($unit->created_at->toDateTimeString())
        ->and($data->updated_at)
        ->toBe($unit->updated_at->toDateTimeString());
});
