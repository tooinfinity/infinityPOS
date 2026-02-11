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

it('filters by search scope on name', function (): void {
    Supplier::factory()->create(['name' => 'John Doe', 'company_name' => 'Acme Inc', 'email' => 'john@example.com', 'phone' => '1234567890']);
    Supplier::factory()->create(['name' => 'Jane Smith', 'company_name' => 'XYZ Corp', 'email' => 'jane@example.com', 'phone' => '0987654321']);

    $results = Supplier::search('John')->get();

    expect($results)->toHaveCount(1)
        ->first()->name->toBe('John Doe');
});

it('filters by search scope on company name', function (): void {
    Supplier::factory()->create(['name' => 'John Doe', 'company_name' => 'Acme Inc', 'email' => 'john@example.com', 'phone' => '1234567890']);
    Supplier::factory()->create(['name' => 'Jane Smith', 'company_name' => 'XYZ Corp', 'email' => 'jane@example.com', 'phone' => '0987654321']);

    $results = Supplier::search('Acme')->get();

    expect($results)->toHaveCount(1)
        ->first()->company_name->toBe('Acme Inc');
});

it('filters by search scope on email', function (): void {
    Supplier::factory()->create(['name' => 'John Doe', 'company_name' => 'Acme Inc', 'email' => 'john@example.com', 'phone' => '1234567890']);
    Supplier::factory()->create(['name' => 'Jane Smith', 'company_name' => 'XYZ Corp', 'email' => 'jane@example.com', 'phone' => '0987654321']);

    $results = Supplier::search('john@example.com')->get();

    expect($results)->toHaveCount(1)
        ->first()->email->toBe('john@example.com');
});

it('filters by search scope on phone', function (): void {
    Supplier::factory()->create(['name' => 'John Doe', 'company_name' => 'Acme Inc', 'email' => 'john@example.com', 'phone' => '1234567890']);
    Supplier::factory()->create(['name' => 'Jane Smith', 'company_name' => 'XYZ Corp', 'email' => 'jane@example.com', 'phone' => '0987654321']);

    $results = Supplier::search('1234567890')->get();

    expect($results)->toHaveCount(1)
        ->first()->phone->toBe('1234567890');
});
