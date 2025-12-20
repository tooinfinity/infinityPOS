<?php

declare(strict_types=1);

use App\Actions\Units\DeleteUnit;
use App\Models\Unit;
use App\Models\User;

it('may delete a unit', function (): void {
    $user = User::factory()->create();
    $unit = Unit::factory()->create(['created_by' => $user->id]);

    $action = resolve(DeleteUnit::class);
    $action->handle($unit);

    expect(Unit::query()->find($unit->id))->toBeNull()
        ->and($unit->created_by)->toBeNull();
});
