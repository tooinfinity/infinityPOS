<?php

declare(strict_types=1);

use App\Actions\Inventory\CreateStockTransfer;
use App\Data\Inventory\CreateStockTransferData;
use App\Data\Inventory\CreateStockTransferItemData;
use App\Enums\StockTransferStatusEnum;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\Store;
use App\Models\User;

it('may create a stock transfer', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $fromStore = Store::factory()->create(['created_by' => $user->id]);
    $toStore = Store::factory()->create(['created_by' => $user->id]);
    $action = resolve(CreateStockTransfer::class);

    $itemData = CreateStockTransferItemData::from([
        'product_id' => $product->id,
        'quantity' => 20,
        'batch_number' => 'BATCH-001',
    ]);

    $data = CreateStockTransferData::from([
        'reference' => 'TR-001',
        'from_store_id' => $fromStore->id,
        'to_store_id' => $toStore->id,
        'notes' => 'Test transfer',
        'items' => [$itemData],
        'created_by' => $user->id,
    ]);

    $transfer = $action->handle($data);

    expect($transfer)->toBeInstanceOf(StockTransfer::class)
        ->and($transfer->reference)->toBe('TR-001')
        ->and($transfer->from_store_id)->toBe($fromStore->id)
        ->and($transfer->to_store_id)->toBe($toStore->id)
        ->and($transfer->status)->toBe(StockTransferStatusEnum::PENDING)
        ->and($transfer->notes)->toBe('Test transfer')
        ->and($transfer->created_by)->toBe($user->id)
        ->and($transfer->items)->toHaveCount(1)
        ->and($transfer->items->first()->product_id)->toBe($product->id)
        ->and($transfer->items->first()->quantity)->toBe(20);
});

it('may create transfer with multiple items', function (): void {
    $user = User::factory()->create();
    $product1 = Product::factory()->create(['created_by' => $user->id]);
    $product2 = Product::factory()->create(['created_by' => $user->id]);
    $fromStore = Store::factory()->create(['created_by' => $user->id]);
    $toStore = Store::factory()->create(['created_by' => $user->id]);
    $action = resolve(CreateStockTransfer::class);

    $data = CreateStockTransferData::from([
        'reference' => 'TR-002',
        'from_store_id' => $fromStore->id,
        'to_store_id' => $toStore->id,
        'notes' => null,
        'items' => [
            CreateStockTransferItemData::from([
                'product_id' => $product1->id,
                'quantity' => 10,
                'batch_number' => null,
            ]),
            CreateStockTransferItemData::from([
                'product_id' => $product2->id,
                'quantity' => 15,
                'batch_number' => 'BATCH-X',
            ]),
        ],
        'created_by' => $user->id,
    ]);

    $transfer = $action->handle($data);

    expect($transfer->items)->toHaveCount(2);
});
