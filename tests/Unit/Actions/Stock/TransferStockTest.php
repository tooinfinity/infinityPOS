<?php

declare(strict_types=1);

use App\Actions\Stock\TransferResult;
use App\Actions\Stock\TransferStock;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\WarehouseSameException;
use App\Models\Batch;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Unit;
use App\Models\Warehouse;

describe(TransferResult::class, function (): void {
    it('may create a transfer result', function (): void {
        $source = Batch::factory()->create();
        $destination = Batch::factory()->create();

        $result = new TransferResult(
            source: $source,
            destination: $destination,
        );

        expect($result)->toBeInstanceOf(TransferResult::class)
            ->and($result->source)->toBe($source)
            ->and($result->destination)->toBe($destination);
    });

    it('has readonly properties', function (): void {
        $source = Batch::factory()->create();
        $destination = Batch::factory()->create();

        $result = new TransferResult(
            source: $source,
            destination: $destination,
        );

        expect($result->source)->toBe($source)
            ->and($result->destination)->toBe($destination);
    });
});

describe(TransferStock::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->product = Product::factory()->for($this->unit)->create();
        $this->sourceWarehouse = Warehouse::factory()->create();
        $this->destinationWarehouse = Warehouse::factory()->create();
        $this->sourceBatch = Batch::factory()
            ->forProduct($this->product)
            ->forWarehouse($this->sourceWarehouse)
            ->create(['quantity' => 100, 'cost_amount' => 5000]);
    });

    it('may transfer stock between warehouses', function (): void {
        $transfer = StockTransfer::factory()
            ->betweenWarehouses($this->sourceWarehouse, $this->destinationWarehouse)
            ->pending()
            ->create();

        StockTransferItem::factory()
            ->forStockTransfer($transfer)
            ->forProduct($this->product)
            ->forBatch($this->sourceBatch)
            ->create(['quantity' => 20]);

        $action = resolve(TransferStock::class);

        $result = $action->handle(
            sourceBatch: $this->sourceBatch,
            destinationWarehouseId: $this->destinationWarehouse->id,
            quantity: 20,
            transfer: $transfer,
        );

        expect($result)->toBeInstanceOf(TransferResult::class)
            ->and($result->source->quantity)->toBe(80)
            ->and($result->destination->quantity)->toBe(20);
    });

    it('creates destination batch if not exists', function (): void {
        $transfer = StockTransfer::factory()
            ->betweenWarehouses($this->sourceWarehouse, $this->destinationWarehouse)
            ->pending()
            ->create();

        expect(Batch::query()
            ->where('product_id', $this->product->id)
            ->where('warehouse_id', $this->destinationWarehouse->id)
            ->exists())->toBeFalse();

        $action = resolve(TransferStock::class);

        $result = $action->handle(
            sourceBatch: $this->sourceBatch,
            destinationWarehouseId: $this->destinationWarehouse->id,
            quantity: 25,
            transfer: $transfer,
        );

        expect($result->destination)->toBeInstanceOf(Batch::class)
            ->and($result->destination->product_id)->toBe($this->product->id)
            ->and($result->destination->warehouse_id)->toBe($this->destinationWarehouse->id)
            ->and($result->destination->cost_amount)->toBe($this->sourceBatch->cost_amount)
            ->and($result->destination->quantity)->toBe(25);
    });

    it('reuses existing destination batch if exists', function (): void {
        $existingDestinationBatch = Batch::factory()
            ->forProduct($this->product)
            ->forWarehouse($this->destinationWarehouse)
            ->create(['quantity' => 10, 'cost_amount' => 5000]);

        $transfer = StockTransfer::factory()
            ->betweenWarehouses($this->sourceWarehouse, $this->destinationWarehouse)
            ->pending()
            ->create();

        $action = resolve(TransferStock::class);

        $result = $action->handle(
            sourceBatch: $this->sourceBatch,
            destinationWarehouseId: $this->destinationWarehouse->id,
            quantity: 15,
            transfer: $transfer,
        );

        expect($result->destination->id)->toBe($existingDestinationBatch->id)
            ->and($result->destination->quantity)->toBe(25);
    });

    it('throws exception when transferring to same warehouse', function (): void {
        $transfer = StockTransfer::factory()
            ->betweenWarehouses($this->sourceWarehouse, $this->sourceWarehouse)
            ->pending()
            ->create();

        $action = resolve(TransferStock::class);

        expect(fn () => $action->handle(
            sourceBatch: $this->sourceBatch,
            destinationWarehouseId: $this->sourceWarehouse->id,
            quantity: 10,
            transfer: $transfer,
        ))->toThrow(WarehouseSameException::class);
    });

    it('throws exception when insufficient stock', function (): void {
        $transfer = StockTransfer::factory()
            ->betweenWarehouses($this->sourceWarehouse, $this->destinationWarehouse)
            ->pending()
            ->create();

        $action = resolve(TransferStock::class);

        expect(fn () => $action->handle(
            sourceBatch: $this->sourceBatch,
            destinationWarehouseId: $this->destinationWarehouse->id,
            quantity: 150, // More than available
            transfer: $transfer,
        ))->toThrow(InsufficientStockException::class);
    });

    it('records stock movement for source warehouse', function (): void {
        $transfer = StockTransfer::factory()
            ->betweenWarehouses($this->sourceWarehouse, $this->destinationWarehouse)
            ->pending()
            ->create();

        $action = resolve(TransferStock::class);

        $action->handle(
            sourceBatch: $this->sourceBatch,
            destinationWarehouseId: $this->destinationWarehouse->id,
            quantity: 30,
            transfer: $transfer,
        );

        expect($this->sourceBatch->fresh()->stockMovements()->count())->toBe(1)
            ->and($this->sourceBatch->fresh()->stockMovements()->first()->quantity)->toBe(-30);
    });

    it('records stock movement for destination warehouse', function (): void {
        $transfer = StockTransfer::factory()
            ->betweenWarehouses($this->sourceWarehouse, $this->destinationWarehouse)
            ->pending()
            ->create();

        $action = resolve(TransferStock::class);

        $result = $action->handle(
            sourceBatch: $this->sourceBatch,
            destinationWarehouseId: $this->destinationWarehouse->id,
            quantity: 30,
            transfer: $transfer,
        );

        expect($result->destination->stockMovements()->count())->toBe(1)
            ->and($result->destination->stockMovements()->first()->quantity)->toBe(30);
    });

    it('preserves expiry date when creating destination batch', function (): void {
        $sourceBatchWithExpiry = Batch::factory()
            ->forProduct($this->product)
            ->forWarehouse($this->sourceWarehouse)
            ->create([
                'quantity' => 100,
                'expires_at' => now()->addMonths(6),
            ]);

        $transfer = StockTransfer::factory()
            ->betweenWarehouses($this->sourceWarehouse, $this->destinationWarehouse)
            ->pending()
            ->create();

        $action = resolve(TransferStock::class);

        $result = $action->handle(
            sourceBatch: $sourceBatchWithExpiry,
            destinationWarehouseId: $this->destinationWarehouse->id,
            quantity: 20,
            transfer: $transfer,
        );

        expect($result->destination->expires_at->toDateString())
            ->toBe($sourceBatchWithExpiry->expires_at->toDateString());
    });

    it('works within a transaction', function (): void {
        $transfer = StockTransfer::factory()
            ->betweenWarehouses($this->sourceWarehouse, $this->destinationWarehouse)
            ->pending()
            ->create();

        $initialQuantity = $this->sourceBatch->quantity;

        Illuminate\Support\Facades\DB::beginTransaction();

        try {
            $action = resolve(TransferStock::class);

            $action->handle(
                sourceBatch: $this->sourceBatch,
                destinationWarehouseId: $this->destinationWarehouse->id,
                quantity: 50,
                transfer: $transfer,
            );

            throw new Exception('Force rollback');
        } catch (Exception) {
            Illuminate\Support\Facades\DB::rollBack();
        }

        expect($this->sourceBatch->fresh()->quantity)->toBe($initialQuantity);
    });
});
