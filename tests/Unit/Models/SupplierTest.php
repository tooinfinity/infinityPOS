<?php

declare(strict_types=1);

use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

test('to array', function (): void {
    $supplier = Supplier::factory()->create()->refresh();

    expect(array_keys($supplier->toArray()))
        ->toBe([
            'id',
            'name',
            'company_name',
            'email',
            'phone',
            'address',
            'city',
            'country',
            'is_active',
            'created_at',
            'updated_at',
        ]);
});

test('only returns active suppliers by default', function (): void {
    Supplier::factory()->count(2)->create([
        'is_active' => true,
    ]);
    Supplier::factory()->count(2)->create([
        'is_active' => false,
    ]);

    $suppliers = Supplier::all();

    expect($suppliers)
        ->toHaveCount(2);
});

it('has many purchases', function (): void {
    $supplier = new Supplier();

    expect($supplier->purchases())
        ->toBeInstanceOf(HasMany::class);
});

it('can create purchases', function (): void {
    $supplier = Supplier::factory()->create();
    Purchase::factory()->count(3)->create(['supplier_id' => $supplier->id]);

    expect($supplier->purchases)
        ->toHaveCount(3)
        ->each->toBeInstanceOf(Purchase::class);
});

it('returns empty collection when no purchases exist', function (): void {
    $supplier = Supplier::factory()->create();

    expect($supplier->purchases)
        ->toBeEmpty()
        ->toBeInstanceOf(Collection::class);
});
