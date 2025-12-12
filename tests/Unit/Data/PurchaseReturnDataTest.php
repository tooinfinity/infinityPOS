<?php

declare(strict_types=1);

use App\Data\PurchaseData;
use App\Data\PurchaseReturnData;
use App\Data\StoreData;
use App\Data\SupplierData;
use App\Data\UserData;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;

it('transforms a purchase return model into PurchaseReturnData', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();
    $purchase = Purchase::factory()->create();
    $supplier = Supplier::factory()->create();
    $store = Store::factory()->create();

    /** @var PurchaseReturn $return */
    $return = PurchaseReturn::factory()
        ->for($creator, 'creator')
        ->for($updater, 'updater')
        ->for($purchase, 'purchase')
        ->for($supplier, 'supplier')
        ->for($store, 'store')
        ->create([
            'reference' => 'PR-0001',
            'total' => 3000,
            'refunded' => 1000,
            'status' => App\Enums\PurchaseReturnStatusEnum::PENDING->value,
            'reason' => 'Defective',
            'notes' => 'Awaiting supplier approval',
        ]);

    $data = PurchaseReturnData::from(
        $return->load(['creator', 'updater', 'purchase', 'supplier', 'store'])
    );

    expect($data)
        ->toBeInstanceOf(PurchaseReturnData::class)
        ->id->toBe($return->id)
        ->reference->toBe('PR-0001')
        ->total->toBe(3000)
        ->refunded->toBe(1000)
        ->status->toBe(App\Enums\PurchaseReturnStatusEnum::PENDING)
        ->reason->toBe('Defective')
        ->notes->toBe('Awaiting supplier approval')
        ->and($data->purchase->resolve())
        ->toBeInstanceOf(PurchaseData::class)
        ->id->toBe($purchase->id)
        ->and($data->supplier->resolve())
        ->toBeInstanceOf(SupplierData::class)
        ->id->toBe($supplier->id)
        ->and($data->store->resolve())
        ->toBeInstanceOf(StoreData::class)
        ->id->toBe($store->id)
        ->and($data->creator->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($creator->id)
        ->and($data->updater->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($updater->id)
        ->and($data->created_at)
        ->toBe($return->created_at->toDateTimeString())
        ->and($data->updated_at)
        ->toBe($return->updated_at->toDateTimeString());
});
