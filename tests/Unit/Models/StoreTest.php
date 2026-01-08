<?php

declare(strict_types=1);

use App\Models\Store;
use Illuminate\Database\Eloquent\Relations\HasMany;

test('to array', function (): void {
    $store = Store::factory()->create()->refresh();

    expect(array_keys($store->toArray()))
        ->toBe([
            'id',
            'name',
            'address',
            'phone',
            'currency',
            'created_at',
            'updated_at',
        ]);
});

test('users relationship returns has many', function (): void {
    $store = new Store();

    expect($store->users())
        ->toBeInstanceOf(HasMany::class);
});

test('cash registers relationship returns has many', function (): void {
    $store = new Store();

    expect($store->cashRegisters())
        ->toBeInstanceOf(HasMany::class);
});

test('inventory relationship returns has many', function (): void {
    $store = new Store();

    expect($store->inventory())
        ->toBeInstanceOf(HasMany::class);
});

test('inventory batches relationship returns has many', function (): void {
    $store = new Store();

    expect($store->inventoryBatches())
        ->toBeInstanceOf(HasMany::class);
});

test('purchases relationship returns has many', function (): void {
    $store = new Store();

    expect($store->purchases())
        ->toBeInstanceOf(HasMany::class);
});

test('sales relationship returns has many', function (): void {
    $store = new Store();

    expect($store->sales())
        ->toBeInstanceOf(HasMany::class);
});

test('invoices relationship returns has many', function (): void {
    $store = new Store();

    expect($store->invoices())
        ->toBeInstanceOf(HasMany::class);
});

test('returns relationship returns has many', function (): void {
    $store = new Store();

    expect($store->returns())
        ->toBeInstanceOf(HasMany::class);
});

test('stock adjustments relationship returns has many', function (): void {
    $store = new Store();

    expect($store->stockAdjustments())
        ->toBeInstanceOf(HasMany::class);
});

test('expenses relationship returns has many', function (): void {
    $store = new Store();

    expect($store->expenses())
        ->toBeInstanceOf(HasMany::class);
});

test('casts returns correct array', function (): void {
    $store = new Store();

    expect($store->casts())
        ->toBe([
            'id' => 'integer',
            'name' => 'string',
            'address' => 'string',
            'phone' => 'string',
            'currency' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ]);
});

test('casts work correctly', function (): void {
    $store = Store::factory()->create()->refresh();

    expect($store->id)->toBeInt()
        ->and($store->name)->toBeString()
        ->and($store->currency)->toBeString()
        ->and($store->created_at)->toBeInstanceOf(DateTimeInterface::class)
        ->and($store->updated_at)->toBeInstanceOf(DateTimeInterface::class);
});
