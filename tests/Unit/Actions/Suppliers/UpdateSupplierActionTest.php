<?php

declare(strict_types=1);

use App\Actions\Suppliers\UpdateSupplier;
use App\Data\Suppliers\UpdateSupplierData;
use App\Models\BusinessIdentifier;
use App\Models\Supplier;
use App\Models\User;

it('may update a supplier', function (): void {
    $user = User::factory()->create();
    $supplier = Supplier::factory()->create([
        'name' => 'Old Supplier',
        'phone' => '+1111111111',
        'email' => 'old@supplier.com',
        'address' => 'Old Address',
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    $user2 = User::factory()->create();
    $action = resolve(UpdateSupplier::class);

    $data = UpdateSupplierData::from([
        'name' => 'Updated Supplier',
        'phone' => '+9999999999',
        'email' => 'updated@supplier.com',
        'address' => 'Updated Address',
        'is_active' => false,
        'updated_by' => $user2->id,
    ]);

    $action->handle($supplier, $data);

    expect($supplier->refresh()->name)->toBe('Updated Supplier')
        ->and($supplier->phone)->toBe('+9999999999')
        ->and($supplier->email)->toBe('updated@supplier.com')
        ->and($supplier->address)->toBe('Updated Address')
        ->and($supplier->is_active)->toBeFalse()
        ->and($supplier->updated_by)->toBe($user2->id);
});
