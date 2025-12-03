<?php

declare(strict_types=1);

use App\Models\Unit;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();

    $unit = Unit::factory()->create(['created_by' => $user->id])->refresh();

    expect(array_keys($unit->toArray()))
        ->toBe([
            'id',
            'name',
            'short_name',
            'is_active',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});
