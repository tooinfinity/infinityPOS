<?php

declare(strict_types=1);

use App\Actions\Supplier\CreateSupplier;
use App\Data\Supplier\CreateSupplierData;
use App\Models\Supplier;

it('may create a supplier', function (): void {
    $action = resolve(CreateSupplier::class);

    $data = new CreateSupplierData(
        name: 'Test Supplier',
        company_name: 'Test Company',
        email: 'test@example.com',
        phone: '1234567890',
        address: '123 Test St',
        city: 'Test City',
        country: 'Test Country',
        is_active: true,
    );

    $supplier = $action->handle($data);

    expect($supplier)->toBeInstanceOf(Supplier::class)
        ->and($supplier->name)->toBe('Test Supplier')
        ->and($supplier->company_name)->toBe('Test Company')
        ->and($supplier->email)->toBe('test@example.com')
        ->and($supplier->phone)->toBe('1234567890')
        ->and($supplier->address)->toBe('123 Test St')
        ->and($supplier->city)->toBe('Test City')
        ->and($supplier->country)->toBe('Test Country')
        ->and($supplier->is_active)->toBeTrue()
        ->and($supplier->exists)->toBeTrue();
});

it('creates supplier with minimal fields', function (): void {
    $action = resolve(CreateSupplier::class);

    $data = new CreateSupplierData(
        name: 'Minimal Supplier',
        company_name: null,
        email: null,
        phone: null,
        address: null,
        city: null,
        country: null,
        is_active: true,
    );

    $supplier = $action->handle($data);

    expect($supplier->name)->toBe('Minimal Supplier')
        ->and($supplier->company_name)->toBeNull()
        ->and($supplier->email)->toBeNull()
        ->and($supplier->phone)->toBeNull()
        ->and($supplier->address)->toBeNull()
        ->and($supplier->city)->toBeNull()
        ->and($supplier->country)->toBeNull()
        ->and($supplier->is_active)->toBeTrue();
});

it('creates supplier with is_active false', function (): void {
    $action = resolve(CreateSupplier::class);

    $data = new CreateSupplierData(
        name: 'Inactive Supplier',
        company_name: null,
        email: null,
        phone: null,
        address: null,
        city: null,
        country: null,
        is_active: false,
    );

    $supplier = $action->handle($data);

    expect($supplier->is_active)->toBeFalse();
});

it('stores supplier in database', function (): void {
    $action = resolve(CreateSupplier::class);

    $data = new CreateSupplierData(
        name: 'Database Supplier',
        company_name: 'DB Company',
        email: 'db@example.com',
        phone: '5555555555',
        address: '789 DB St',
        city: 'DB City',
        country: 'DB Country',
        is_active: true,
    );

    $supplier = $action->handle($data);

    $this->assertDatabaseHas('suppliers', [
        'id' => $supplier->id,
        'name' => 'Database Supplier',
        'company_name' => 'DB Company',
        'email' => 'db@example.com',
        'phone' => '5555555555',
        'address' => '789 DB St',
        'city' => 'DB City',
        'country' => 'DB Country',
        'is_active' => true,
    ]);
});
