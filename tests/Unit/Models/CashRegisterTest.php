<?php

declare(strict_types=1);

use App\Models\CashRegister;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

test('to array', function (): void {
    $cashRegister = CashRegister::factory()->create()->refresh();

    expect(array_keys($cashRegister->toArray()))
        ->toBe([
            'id',
            'store_id',
            'name',
            'description',
            'is_active',
            'created_at',
            'updated_at',
        ]);
});

test('store relationship returns belongs to', function (): void {
    $cashRegister = new CashRegister();

    expect($cashRegister->store())
        ->toBeInstanceOf(BelongsTo::class);
});

test('sessions relationship returns has many', function (): void {
    $cashRegister = new CashRegister();

    expect($cashRegister->sessions())
        ->toBeInstanceOf(HasMany::class);
});

test('casts returns correct array', function (): void {
    $cashRegister = new CashRegister();

    expect($cashRegister->casts())
        ->toBe([
            'id' => 'integer',
            'store_id' => 'integer',
            'name' => 'string',
            'description' => 'string',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ]);
});

test('casts work correctly', function (): void {
    $cashRegister = CashRegister::factory()->create([
        'is_active' => true,
    ])->refresh();

    expect($cashRegister->is_active)->toBeTrue()
        ->and($cashRegister->id)->toBeInt()
        ->and($cashRegister->store_id)->toBeInt()
        ->and($cashRegister->name)->toBeString()
        ->and($cashRegister->created_at)->toBeInstanceOf(DateTimeInterface::class);
});
