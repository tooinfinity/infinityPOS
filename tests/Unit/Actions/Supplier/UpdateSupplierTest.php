<?php

declare(strict_types=1);

use App\Actions\Supplier\UpdateSupplier;
use App\Data\Supplier\SupplierData;
use App\Models\Supplier;

it('may update a supplier name', function (): void {
    $supplier = Supplier::factory()->create([
        'name' => 'Old Name',
    ]);

    $action = resolve(UpdateSupplier::class);

    $data = new SupplierData(
        name: 'New Name',
        company_name: $supplier->company_name,
        email: $supplier->email,
        phone: $supplier->phone,
        address: $supplier->address,
        city: $supplier->city,
        country: $supplier->country,
        is_active: $supplier->is_active,
    );

    $updatedSupplier = $action->handle($supplier, $data);

    expect($updatedSupplier->name)->toBe('New Name');
});

it('may update all supplier fields', function (): void {
    $supplier = Supplier::factory()->create([
        'name' => 'Old Name',
        'company_name' => 'Old Company',
        'email' => 'old@example.com',
        'phone' => '1111111111',
        'address' => 'Old Address',
        'city' => 'Old City',
        'country' => 'Old Country',
        'is_active' => true,
    ]);

    $action = resolve(UpdateSupplier::class);

    $data = new SupplierData(
        name: 'New Name',
        company_name: 'New Company',
        email: 'new@example.com',
        phone: '2222222222',
        address: 'New Address',
        city: 'New City',
        country: 'New Country',
        is_active: false,
    );

    $updatedSupplier = $action->handle($supplier, $data);

    expect($updatedSupplier->name)->toBe('New Name')
        ->and($updatedSupplier->company_name)->toBe('New Company')
        ->and($updatedSupplier->email)->toBe('new@example.com')
        ->and($updatedSupplier->phone)->toBe('2222222222')
        ->and($updatedSupplier->address)->toBe('New Address')
        ->and($updatedSupplier->city)->toBe('New City')
        ->and($updatedSupplier->country)->toBe('New Country')
        ->and($updatedSupplier->is_active)->toBeFalse();
});

it('partially updates supplier with Optional fields', function (): void {
    $supplier = Supplier::factory()->create([
        'name' => 'Original Name',
        'company_name' => 'Original Company',
        'email' => 'original@example.com',
        'phone' => '3333333333',
        'is_active' => true,
    ]);

    $action = resolve(UpdateSupplier::class);

    $data = new SupplierData(
        name: $supplier->name,
        company_name: $supplier->company_name,
        email: 'updated@example.com',
        phone: $supplier->phone,
        address: $supplier->address,
        city: $supplier->city,
        country: $supplier->country,
        is_active: false,
    );

    $updatedSupplier = $action->handle($supplier, $data);

    expect($updatedSupplier->name)->toBe('Original Name')
        ->and($updatedSupplier->company_name)->toBe('Original Company')
        ->and($updatedSupplier->email)->toBe('updated@example.com')
        ->and($updatedSupplier->phone)->toBe('3333333333')
        ->and($updatedSupplier->is_active)->toBeFalse();
});

it('updates nullable fields to null', function (): void {
    $supplier = Supplier::factory()->create([
        'company_name' => 'Company Name',
        'email' => 'email@example.com',
        'phone' => '4444444444',
    ]);

    $action = resolve(UpdateSupplier::class);

    $data = new SupplierData(
        name: $supplier->name,
        company_name: null,
        email: null,
        phone: null,
        address: $supplier->address,
        city: $supplier->city,
        country: $supplier->country,
        is_active: $supplier->is_active,
    );

    $updatedSupplier = $action->handle($supplier, $data);

    expect($updatedSupplier->company_name)->toBeNull()
        ->and($updatedSupplier->email)->toBeNull()
        ->and($updatedSupplier->phone)->toBeNull();
});

it('persists updates to database', function (): void {
    $supplier = Supplier::factory()->create([
        'name' => 'Original Name',
    ]);

    $action = resolve(UpdateSupplier::class);

    $data = new SupplierData(
        name: 'Persisted Name',
        company_name: $supplier->company_name,
        email: $supplier->email,
        phone: $supplier->phone,
        address: $supplier->address,
        city: $supplier->city,
        country: $supplier->country,
        is_active: $supplier->is_active,
    );

    $action->handle($supplier, $data);

    $this->assertDatabaseHas('suppliers', [
        'id' => $supplier->id,
        'name' => 'Persisted Name',
    ]);
});
