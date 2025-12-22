<?php

declare(strict_types=1);

use App\Actions\Purchases\UpdatePurchase;
use App\Data\Purchases\UpdatePurchaseData;
use App\Models\Purchase;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;

it('may update a purchase', function (): void {
    $user = User::factory()->create();
    $supplier = Supplier::factory()->create(['created_by' => $user->id]);
    $newSupplier = Supplier::factory()->create(['created_by' => $user->id]);
    $store = Store::factory()->create(['created_by' => $user->id]);
    $purchase = Purchase::factory()->create([
        'supplier_id' => $supplier->id,
        'store_id' => $store->id,
        'reference' => 'PO-001',
        'notes' => 'Old notes',
        'created_by' => $user->id,
    ]);

    $action = resolve(UpdatePurchase::class);

    $data = UpdatePurchaseData::from([
        'reference' => 'PO-002',
        'supplier_id' => $newSupplier->id,
        'store_id' => null,
        'subtotal' => null,
        'discount' => null,
        'tax' => null,
        'total' => null,
        'notes' => 'Updated notes',
        'updated_by' => $user->id,
    ]);

    $updatedPurchase = $action->handle($purchase, $data);

    expect($updatedPurchase->reference)->toBe('PO-002')
        ->and($updatedPurchase->supplier_id)->toBe($newSupplier->id)
        ->and($updatedPurchase->notes)->toBe('Updated notes')
        ->and($updatedPurchase->updated_by)->toBe($user->id);
});
