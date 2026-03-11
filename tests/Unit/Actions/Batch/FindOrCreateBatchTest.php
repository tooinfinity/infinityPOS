<?php

declare(strict_types=1);

use App\Actions\Batch\FindOrCreateBatch;
use App\Models\Batch;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Warehouse;

describe(FindOrCreateBatch::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->product = Product::factory()->for($this->unit)->create();
        $this->warehouse = Warehouse::factory()->create();
    });

    it('finds existing batch with matching criteria', function (): void {
        $existingBatch = Batch::factory()
            ->forProduct($this->product)
            ->forWarehouse($this->warehouse)
            ->create([
                'cost_amount' => 5000,
                'expires_at' => null,
                'quantity' => 100,
            ]);

        $action = resolve(FindOrCreateBatch::class);

        $batch = $action->handle(
            productId: $this->product->id,
            warehouseId: $this->warehouse->id,
            costAmount: 5000,
        );

        expect($batch->id)->toBe($existingBatch->id)
            ->and($batch->quantity)->toBe(100);
    });

    it('creates new batch when no match found', function (): void {
        $action = resolve(FindOrCreateBatch::class);

        $batch = $action->handle(
            productId: $this->product->id,
            warehouseId: $this->warehouse->id,
            costAmount: 6000,
        );

        expect($batch)->toBeInstanceOf(Batch::class)
            ->and($batch->product_id)->toBe($this->product->id)
            ->and($batch->warehouse_id)->toBe($this->warehouse->id)
            ->and($batch->cost_amount)->toBe(6000)
            ->and($batch->quantity)->toBe(0)
            ->and($batch->expires_at)->toBeNull();
    });

    it('matches batch with expiry date', function (): void {
        $expiryDate = now()->addMonths(6);

        $existingBatch = Batch::factory()
            ->forProduct($this->product)
            ->forWarehouse($this->warehouse)
            ->create([
                'cost_amount' => 5000,
                'expires_at' => $expiryDate,
            ]);

        $action = resolve(FindOrCreateBatch::class);

        $batch = $action->handle(
            productId: $this->product->id,
            warehouseId: $this->warehouse->id,
            costAmount: 5000,
            expiresAt: $expiryDate,
        );

        expect($batch->id)->toBe($existingBatch->id);
    });

    it('does not match batch with different cost amount', function (): void {
        Batch::factory()
            ->forProduct($this->product)
            ->forWarehouse($this->warehouse)
            ->create([
                'cost_amount' => 5000,
                'expires_at' => null,
            ]);

        $action = resolve(FindOrCreateBatch::class);

        $batch = $action->handle(
            productId: $this->product->id,
            warehouseId: $this->warehouse->id,
            costAmount: 6000,
        );

        expect($batch->id)->not->toBe(Batch::query()->first()?->id)
            ->and($batch->cost_amount)->toBe(6000);
    });

    it('does not match batch with different expiry date', function (): void {
        $expiryDate1 = now()->addMonths(6);
        $expiryDate2 = now()->addMonths(12);

        Batch::factory()
            ->forProduct($this->product)
            ->forWarehouse($this->warehouse)
            ->create([
                'cost_amount' => 5000,
                'expires_at' => $expiryDate1,
            ]);

        $action = resolve(FindOrCreateBatch::class);

        $batch = $action->handle(
            productId: $this->product->id,
            warehouseId: $this->warehouse->id,
            costAmount: 5000,
            expiresAt: $expiryDate2,
        );

        expect($batch->id)->not->toBe(Batch::query()->first()?->id);
    });

    it('distinguishes between null and non-null expiry dates', function (): void {
        Batch::factory()
            ->forProduct($this->product)
            ->forWarehouse($this->warehouse)
            ->create([
                'cost_amount' => 5000,
                'expires_at' => null,
            ]);

        $action = resolve(FindOrCreateBatch::class);

        $batch = $action->handle(
            productId: $this->product->id,
            warehouseId: $this->warehouse->id,
            costAmount: 5000,
            expiresAt: now()->addMonths(6),
        );

        expect($batch->id)->not->toBe(Batch::query()->first()?->id);
    });

    it('generates batch number for new batch', function (): void {
        $action = resolve(FindOrCreateBatch::class);

        $batch = $action->handle(
            productId: $this->product->id,
            warehouseId: $this->warehouse->id,
            costAmount: 5000,
        );

        expect($batch->batch_number)->toStartWith('BAT-')
            ->and($batch->batch_number)->toContain((string) $this->product->id);
    });

    it('creates batch with zero quantity', function (): void {
        $action = resolve(FindOrCreateBatch::class);

        $batch = $action->handle(
            productId: $this->product->id,
            warehouseId: $this->warehouse->id,
            costAmount: 5000,
        );

        expect($batch->quantity)->toBe(0);
    });

    it('works within a transaction', function (): void {
        $action = resolve(FindOrCreateBatch::class);

        Illuminate\Support\Facades\DB::beginTransaction();

        try {
            $batch = $action->handle(
                productId: $this->product->id,
                warehouseId: $this->warehouse->id,
                costAmount: 5000,
            );

            throw new Exception('Force rollback');
        } catch (Exception) {
            Illuminate\Support\Facades\DB::rollBack();
        }

        expect(Batch::query()->where('product_id', $this->product->id)->exists())->toBeFalse();
    });
});
