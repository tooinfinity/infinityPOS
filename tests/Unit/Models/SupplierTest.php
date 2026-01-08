<?php

declare(strict_types=1);

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Relations\HasMany;

test('to array', function (): void {
    $supplier = Supplier::factory()->create()->refresh();

    expect(array_keys($supplier->toArray()))
        ->toBe([
            'id',
            'name',
            'contact_person',
            'phone',
            'email',
            'address',
            'created_at',
            'updated_at',
        ]);
});

test('purchases relationship returns has many', function (): void {
    $supplier = new Supplier();

    expect($supplier->purchases())
        ->toBeInstanceOf(HasMany::class);
});

test('casts returns correct array', function (): void {
    $supplier = new Supplier();

    expect($supplier->casts())
        ->toBe([
            'id' => 'integer',
            'name' => 'string',
            'contact_person' => 'string',
            'phone' => 'string',
            'email' => 'string',
            'address' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ]);
});

test('casts work correctly', function (): void {
    $supplier = Supplier::factory()->create()->refresh();

    expect($supplier->id)->toBeInt()
        ->and($supplier->name)->toBeString()
        ->and($supplier->created_at)->toBeInstanceOf(DateTimeInterface::class)
        ->and($supplier->updated_at)->toBeInstanceOf(DateTimeInterface::class);
});
