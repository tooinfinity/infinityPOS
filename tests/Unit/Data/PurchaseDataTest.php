<?php

declare(strict_types=1);

use App\Data\PurchaseData;
use App\Data\Stores\StoreData;
use App\Data\Suppliers\SupplierData;
use App\Data\Users\UserData;
use App\Models\Purchase;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;

it('transforms a purchase model into PurchaseData', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();
    $supplier = Supplier::factory()->create();
    $store = Store::factory()->create();

    /** @var Purchase $purchase */
    $purchase = Purchase::factory()
        ->for($creator, 'creator')
        ->for($updater, 'updater')
        ->for($supplier, 'supplier')
        ->for($store, 'store')
        ->create([
            'reference' => 'PU-0001',
            'subtotal' => 8000,
            'discount' => 300,
            'tax' => 1500,
            'total' => 9200,
            'paid' => 4000,
            'status' => App\Enums\PurchaseStatusEnum::RECEIVED->value,
            'notes' => 'Delivered on time',
        ]);

    $data = PurchaseData::from(
        $purchase->load(['creator', 'updater', 'supplier', 'store'])
    );

    expect($data)
        ->toBeInstanceOf(PurchaseData::class)
        ->id->toBe($purchase->id)
        ->reference->toBe('PU-0001')
        ->subtotal->toBe(8000)
        ->discount->toBe(300)
        ->tax->toBe(1500)
        ->total->toBe(9200)
        ->paid->toBe(4000)
        ->status->toBe(App\Enums\PurchaseStatusEnum::RECEIVED)
        ->notes->toBe('Delivered on time')
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
        ->toBe($purchase->created_at->toDateTimeString())
        ->and($data->updated_at)
        ->toBe($purchase->updated_at->toDateTimeString());
});
