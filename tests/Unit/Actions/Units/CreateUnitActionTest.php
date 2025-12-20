<?php

declare(strict_types=1);

use App\Actions\Units\CreateUnit;
use App\Data\Units\CreateUnitData;
use App\Models\Unit;
use App\Models\User;

it('may create a unit', function (): void {
    $user = User::factory()->create();
    $action = resolve(CreateUnit::class);

    $data = CreateUnitData::from([
        'name' => 'Kilogram',
        'short_name' => 'kg',
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    $unit = $action->handle($data);

    expect($unit)->toBeInstanceOf(Unit::class)
        ->and($unit->name)->toBe('Kilogram')
        ->and($unit->short_name)->toBe('kg')
        ->and($unit->is_active)->toBeTrue()
        ->and($unit->created_by)->toBe($user->id);
});
