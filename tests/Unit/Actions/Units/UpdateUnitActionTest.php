<?php

declare(strict_types=1);

use App\Actions\Units\UpdateUnit;
use App\Data\Units\UpdateUnitData;
use App\Models\Unit;
use App\Models\User;

it('may update a unit', function (): void {
    $user = User::factory()->create();
    $unit = Unit::factory()->create([
        'name' => 'Old Unit',
        'short_name' => 'old',
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    $user2 = User::factory()->create();
    $action = resolve(UpdateUnit::class);

    $data = UpdateUnitData::from([
        'name' => 'Updated Unit',
        'short_name' => 'upd',
        'is_active' => false,
        'updated_by' => $user2->id,
    ]);

    $action->handle($unit, $data);

    expect($unit->refresh()->name)->toBe('Updated Unit')
        ->and($unit->short_name)->toBe('upd')
        ->and($unit->is_active)->toBeFalse()
        ->and($unit->updated_by)->toBe($user2->id);
});
