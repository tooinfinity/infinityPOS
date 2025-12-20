<?php

declare(strict_types=1);

use App\Actions\Suppliers\CreateSupplier;
use App\Data\Suppliers\CreateSupplierData;
use App\Models\BusinessIdentifier;
use App\Models\Supplier;
use App\Models\User;

it('may create a supplier', function (): void {
    $user = User::factory()->create();
    $action = resolve(CreateSupplier::class);

    $data = CreateSupplierData::from([
        'name' => 'ABC Supplies Inc',
        'phone' => '+1234567890',
        'email' => 'contact@abcsupplies.com',
        'address' => '456 Industrial Pkwy, Chicago',
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    $supplier = $action->handle($data);

    expect($supplier)->toBeInstanceOf(Supplier::class)
        ->and($supplier->name)->toBe('ABC Supplies Inc')
        ->and($supplier->phone)->toBe('+1234567890')
        ->and($supplier->email)->toBe('contact@abcsupplies.com')
        ->and($supplier->address)->toBe('456 Industrial Pkwy, Chicago')
        ->and($supplier->is_active)->toBeTrue()
        ->and($supplier->created_by)->toBe($user->id);
});
