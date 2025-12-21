<?php

declare(strict_types=1);

use App\Data\Suppliers\SupplierData;
use App\Data\Users\UserData;
use App\Models\Supplier;
use App\Models\User;

it('transforms a supplier model into SupplierData', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    /** @var Supplier $supplier */
    $supplier = Supplier::factory()
        ->for($creator, 'creator')
        ->for($updater, 'updater')
        ->create([
            'name' => 'ACME Supplies',
            'phone' => '555-1234',
            'email' => 'contact@acme.test',
            'address' => '42 Supplier St',
            'is_active' => true,
        ]);

    $data = SupplierData::from(
        $supplier->load(['creator', 'updater'])
    );

    expect($data)
        ->toBeInstanceOf(SupplierData::class)
        ->id->toBe($supplier->id)
        ->name->toBe('ACME Supplies')
        ->phone->toBe('555-1234')
        ->email->toBe('contact@acme.test')
        ->address->toBe('42 Supplier St')
        ->is_active->toBeTrue()
        ->and($data->creator->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($creator->id)
        ->and($data->updater->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($updater->id)
        ->and($data->created_at)
        ->toBe($supplier->created_at->toDateTimeString())
        ->and($data->updated_at)
        ->toBe($supplier->updated_at->toDateTimeString());
});
