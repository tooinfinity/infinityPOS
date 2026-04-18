<?php

declare(strict_types=1);

use App\Actions\Stock\AddStock;
use App\Actions\Stock\AdjustStock;
use App\Actions\Stock\DeductStock;
use App\Actions\Stock\RecordStockMovement;
use App\Enums\StockMovementTypeEnum;
use App\Exceptions\InvalidOperationException;
use App\Models\Batch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\Warehouse;

describe(AddStock::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->product = Product::factory()->for($this->unit)->create();
        $this->batch = Batch::factory()->for($this->product)->create(['quantity' => 50]);
        $this->warehouse = Warehouse::factory()->create();
    });

    it('may add stock to a batch', function (): void {
        $action = resolve(AddStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $result = $action->handle($this->batch, 10, $sale, 'Adding stock');

        expect($result->quantity)->toBe(60);
    });

    it('records stock movement when adding stock', function (): void {
        $action = resolve(AddStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $action->handle($this->batch, 10, $sale);

        expect($this->batch->stockMovements()->count())->toBe(1);
    });

    it('stores quantity as positive in movement', function (): void {
        $action = resolve(AddStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $action->handle($this->batch, 25, $sale);

        $movement = $this->batch->fresh()->stockMovements()->first();

        expect($movement->quantity)->toBe(25)
            ->and($movement->quantity)->toBeGreaterThan(0);
    });

    it('uses In movement type', function (): void {
        $action = resolve(AddStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $action->handle($this->batch, 10, $sale);

        $movement = $this->batch->fresh()->stockMovements()->first();

        expect($movement->type)->toBe(StockMovementTypeEnum::In);
    });

    it('uses default note when null is provided', function (): void {
        $action = resolve(AddStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $action->handle($this->batch, 10, $sale);

        $movement = $this->batch->fresh()->stockMovements()->first();

        expect($movement->note)->toBe(StockMovementTypeEnum::In->label());
    });

    it('throws exception when adding zero quantity', function (): void {
        $action = resolve(AddStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        expect(fn () => $action->handle($this->batch, 0, $sale, 'Zero add'))
            ->toThrow(InvalidOperationException::class, 'Quantity must be positive.');
    });

    it('adds to empty batch', function (): void {
        $action = resolve(AddStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();
        $emptyBatch = Batch::factory()->for($this->product)->for($this->warehouse)->create(['quantity' => 0]);

        $result = $action->handle($emptyBatch, 100, $sale);

        expect($result->quantity)->toBe(100);
    });

    it('adds large quantity correctly', function (): void {
        $action = resolve(AddStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $result = $action->handle($this->batch, 999999, $sale);

        expect($result->quantity)->toBe(1000049);
    });

    it('records correct previous quantity in movement', function (): void {
        $action = resolve(AddStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $action->handle($this->batch, 10, $sale);

        $movement = $this->batch->fresh()->stockMovements()->first();

        expect($movement->previous_quantity)->toBe(50)
            ->and($movement->current_quantity)->toBe(60);
    });

    it('works with purchase as reference', function (): void {
        $action = resolve(AddStock::class);
        $purchase = Purchase::factory()->for($this->warehouse)->create();

        $result = $action->handle($this->batch, 15, $purchase, 'Purchase receipt');

        expect($result->quantity)->toBe(65)
            ->and($this->batch->fresh()->stockMovements()->first()->reference)->toBeInstanceOf(Purchase::class);
    });
});

describe(DeductStock::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->product = Product::factory()->for($this->unit)->create();
        $this->batch = Batch::factory()->for($this->product)->create(['quantity' => 50]);
        $this->warehouse = Warehouse::factory()->create();
    });

    it('may deduct stock from a batch', function (): void {
        $action = resolve(DeductStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $result = $action->handle($this->batch, 10, $sale, 'Deducting stock');

        expect($result->quantity)->toBe(40);
    });

    it('throws exception when deducting more than available', function (): void {
        $action = resolve(DeductStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        expect(fn () => $action->handle($this->batch, 100, $sale))
            ->toThrow(App\Exceptions\InsufficientStockException::class);
    });

    it('throws exception when deducting zero quantity', function (): void {
        $action = resolve(DeductStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        expect(fn () => $action->handle($this->batch, 0, $sale))
            ->toThrow(InvalidOperationException::class, 'Quantity must be positive.');
    });

    it('throws exception when deducting negative quantity', function (): void {
        $action = resolve(DeductStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        expect(fn () => $action->handle($this->batch, -10, $sale))
            ->toThrow(InvalidOperationException::class, 'Quantity must be positive.');
    });

    it('records stock movement when deducting stock', function (): void {
        $action = resolve(DeductStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $action->handle($this->batch, 10, $sale);

        expect($this->batch->stockMovements()->count())->toBe(1);
    });

    it('stores quantity as negative in movement', function (): void {
        $action = resolve(DeductStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $action->handle($this->batch, 25, $sale);

        $movement = $this->batch->fresh()->stockMovements()->first();

        expect($movement->quantity)->toBe(-25)
            ->and($movement->quantity)->toBeLessThan(0);
    });

    it('uses Out movement type', function (): void {
        $action = resolve(DeductStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $action->handle($this->batch, 10, $sale);

        $movement = $this->batch->fresh()->stockMovements()->first();

        expect($movement->type)->toBe(StockMovementTypeEnum::Out);
    });

    it('uses default note when null is provided', function (): void {
        $action = resolve(DeductStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $action->handle($this->batch, 10, $sale);

        $movement = $this->batch->fresh()->stockMovements()->first();

        expect($movement->note)->toBe(StockMovementTypeEnum::Out->label());
    });

    it('can fully deplete batch stock', function (): void {
        $action = resolve(DeductStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $result = $action->handle($this->batch, 50, $sale);

        expect($result->quantity)->toBe(0);
    });

    it('deducts exact available quantity', function (): void {
        $action = resolve(DeductStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $result = $action->handle($this->batch, 50, $sale);

        expect($result->quantity)->toBe(0);
    });

    it('throws exception with correct message format', function (): void {
        $action = resolve(DeductStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        expect(fn () => $action->handle($this->batch, 100, $sale))
            ->toThrow(
                App\Exceptions\InsufficientStockException::class,
                "Insufficient stock in batch {$this->batch->id} for product \"{$this->product->name}\". Required: 100, Available: 50"
            );
    });

    it('records correct previous quantity in movement', function (): void {
        $action = resolve(DeductStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $action->handle($this->batch, 10, $sale);

        $movement = $this->batch->fresh()->stockMovements()->first();

        expect($movement->previous_quantity)->toBe(50)
            ->and($movement->current_quantity)->toBe(40);
    });

    it('throws exception when deducting from empty batch', function (): void {
        $action = resolve(DeductStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();
        $emptyBatch = Batch::factory()->for($this->product)->for($this->warehouse)->create(['quantity' => 0]);

        expect(fn () => $action->handle($emptyBatch, 1, $sale))
            ->toThrow(App\Exceptions\InsufficientStockException::class);
    });

    it('prevents partial deduction by throwing on insufficient stock', function (): void {
        $action = resolve(DeductStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        expect(fn () => $action->handle($this->batch, 51, $sale))
            ->toThrow(App\Exceptions\InsufficientStockException::class);

        expect($this->batch->fresh()->quantity)->toBe(50);
    });
});

describe(AdjustStock::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->product = Product::factory()->for($this->unit)->create();
        $this->batch = Batch::factory()->for($this->product)->create(['quantity' => 50]);
        $this->warehouse = Warehouse::factory()->create();
    });

    it('may adjust stock to a new quantity', function (): void {
        $action = resolve(AdjustStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $result = $action->handle($this->batch, 75, $sale, 'Stock adjustment');

        expect($result->quantity)->toBe(75);
    });

    it('records stock movement when adjusting stock up', function (): void {
        $action = resolve(AdjustStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $action->handle($this->batch, 75, $sale, 'Stock adjustment up');

        expect($this->batch->stockMovements()->count())->toBe(1);
    });

    it('records stock movement when adjusting stock down', function (): void {
        $action = resolve(AdjustStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $action->handle($this->batch, 25, $sale, 'Stock adjustment down');

        expect($this->batch->stockMovements()->count())->toBe(1);
    });

    it('returns batch without movement when quantity unchanged', function (): void {
        $action = resolve(AdjustStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $result = $action->handle($this->batch, 50, $sale, 'No change adjustment');

        expect($result->quantity)->toBe(50)
            ->and($this->batch->stockMovements()->count())->toBe(0);
    });

    it('throws exception for negative quantity', function (): void {
        $action = resolve(AdjustStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        expect(fn () => $action->handle($this->batch, -10, $sale))
            ->toThrow(InvalidOperationException::class, 'Cannot adjust Stock. Adjusted quantity cannot be negative.');
    });

    it('stores positive difference when adjusting up', function (): void {
        $action = resolve(AdjustStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $action->handle($this->batch, 75, $sale);

        $movement = $this->batch->fresh()->stockMovements()->first();

        expect($movement->quantity)->toBe(25)
            ->and($movement->quantity)->toBeGreaterThan(0);
    });

    it('stores negative difference when adjusting down', function (): void {
        $action = resolve(AdjustStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $action->handle($this->batch, 30, $sale);

        $movement = $this->batch->fresh()->stockMovements()->first();

        expect($movement->quantity)->toBe(-20)
            ->and($movement->quantity)->toBeLessThan(0);
    });

    it('uses Adjustment movement type', function (): void {
        $action = resolve(AdjustStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $action->handle($this->batch, 75, $sale);

        $movement = $this->batch->fresh()->stockMovements()->first();

        expect($movement->type)->toBe(StockMovementTypeEnum::Adjustment);
    });

    it('generates default note for increase adjustment', function (): void {
        $action = resolve(AdjustStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $action->handle($this->batch, 75, $sale);

        $movement = $this->batch->fresh()->stockMovements()->first();

        expect($movement->note)->toBe('Manual adjustment: +25 units');
    });

    it('generates default note for decrease adjustment', function (): void {
        $action = resolve(AdjustStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $action->handle($this->batch, 30, $sale);

        $movement = $this->batch->fresh()->stockMovements()->first();

        expect($movement->note)->toBe('Manual adjustment: -20 units');
    });

    it('records correct previous quantity in movement', function (): void {
        $action = resolve(AdjustStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $action->handle($this->batch, 60, $sale);

        $movement = $this->batch->fresh()->stockMovements()->first();

        expect($movement->previous_quantity)->toBe(50)
            ->and($movement->current_quantity)->toBe(60);
    });

    it('can adjust empty batch to positive quantity', function (): void {
        $action = resolve(AdjustStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();
        $emptyBatch = Batch::factory()->for($this->product)->for($this->warehouse)->create(['quantity' => 0]);

        $result = $action->handle($emptyBatch, 100, $sale);

        expect($result->quantity)->toBe(100);
    });

    it('can adjust to zero', function (): void {
        $action = resolve(AdjustStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $result = $action->handle($this->batch, 0, $sale);

        expect($result->quantity)->toBe(0);
    });

    it('adjustment works within a transaction', function (): void {
        $action = resolve(AdjustStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();
        $initialQuantity = $this->batch->quantity;

        Illuminate\Support\Facades\DB::beginTransaction();

        try {
            $action->handle($this->batch, 100, $sale);
            throw new Exception('Force rollback');
        } catch (Exception) {
            Illuminate\Support\Facades\DB::rollBack();
        }

        expect($this->batch->fresh()->quantity)->toBe($initialQuantity);
    });
});

describe(RecordStockMovement::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->product = Product::factory()->for($this->unit)->create();
        $this->batch = Batch::factory()->for($this->product)->create(['quantity' => 50]);
        $this->warehouse = Warehouse::factory()->create();
    });

    it('records a stock movement', function (): void {
        $action = resolve(RecordStockMovement::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $result = $action->handle(
            $this->batch,
            StockMovementTypeEnum::In,
            10,
            $sale,
            40,
            'Test movement'
        );

        expect($result)->toBeInstanceOf(StockMovement::class);
        expect($this->batch->stockMovements()->count())->toBe(1);
    });

    it('sets warehouse_id from batch', function (): void {
        $action = resolve(RecordStockMovement::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $movement = $action->handle(
            $this->batch,
            StockMovementTypeEnum::In,
            10,
            $sale,
            40,
        );

        expect($movement->warehouse_id)->toBe($this->batch->warehouse_id);
    });

    it('sets product_id from batch', function (): void {
        $action = resolve(RecordStockMovement::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $movement = $action->handle(
            $this->batch,
            StockMovementTypeEnum::In,
            10,
            $sale,
            40,
        );

        expect($movement->product_id)->toBe($this->batch->product_id);
    });

    it('sets batch_id from batch', function (): void {
        $action = resolve(RecordStockMovement::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $movement = $action->handle(
            $this->batch,
            StockMovementTypeEnum::In,
            10,
            $sale,
            40,
        );

        expect($movement->batch_id)->toBe($this->batch->id);
    });

    it('sets user_id from auth', function (): void {
        $action = resolve(RecordStockMovement::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $movement = $action->handle(
            $this->batch,
            StockMovementTypeEnum::In,
            10,
            $sale,
            40,
        );

        expect($movement->user_id)->toBe(auth()->id());
    });

    it('sets reference morphing correctly', function (): void {
        $action = resolve(RecordStockMovement::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $movement = $action->handle(
            $this->batch,
            StockMovementTypeEnum::In,
            10,
            $sale,
            40,
        );

        expect($movement->reference_type)->toBe($sale->getMorphClass())
            ->and($movement->reference_id)->toBe($sale->id);
    });

    it('sets correct quantity', function (): void {
        $action = resolve(RecordStockMovement::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $movement = $action->handle(
            $this->batch,
            StockMovementTypeEnum::In,
            15,
            $sale,
            40,
        );

        expect($movement->quantity)->toBe(15);
    });

    it('sets correct previous_quantity', function (): void {
        $action = resolve(RecordStockMovement::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $movement = $action->handle(
            $this->batch,
            StockMovementTypeEnum::In,
            10,
            $sale,
            45,
        );

        expect($movement->previous_quantity)->toBe(45);
    });

    it('sets correct current_quantity from batch', function (): void {
        $action = resolve(RecordStockMovement::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $movement = $action->handle(
            $this->batch,
            StockMovementTypeEnum::In,
            10,
            $sale,
            40,
        );

        expect($movement->current_quantity)->toBe($this->batch->quantity);
    });

    it('uses custom note when provided', function (): void {
        $action = resolve(RecordStockMovement::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $movement = $action->handle(
            $this->batch,
            StockMovementTypeEnum::In,
            10,
            $sale,
            40,
            'Custom note',
        );

        expect($movement->note)->toBe('Custom note');
    });

    it('uses type label as default note when null', function (): void {
        $action = resolve(RecordStockMovement::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $movement = $action->handle(
            $this->batch,
            StockMovementTypeEnum::Adjustment,
            -5,
            $sale,
            50,
        );

        expect($movement->note)->toBe(StockMovementTypeEnum::Adjustment->label());
    });

    it('records all movement types correctly', function (): void {
        $action = resolve(RecordStockMovement::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        foreach (StockMovementTypeEnum::cases() as $type) {
            $movement = $action->handle(
                $this->batch,
                $type,
                $type === StockMovementTypeEnum::Out ? -10 : 10,
                $sale,
                40,
            );

            expect($movement->type)->toBe($type);
        }
    });

    it('returns StockMovement model instance', function (): void {
        $action = resolve(RecordStockMovement::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $result = $action->handle(
            $this->batch,
            StockMovementTypeEnum::In,
            10,
            $sale,
            40,
        );

        expect($result)->toBeInstanceOf(StockMovement::class);
    });

    it('can be retrieved through batch relationship', function (): void {
        $action = resolve(RecordStockMovement::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $action->handle($this->batch, StockMovementTypeEnum::In, 10, $sale, 40);

        $freshBatch = $this->batch->fresh();

        expect($freshBatch->stockMovements)->toHaveCount(1)
            ->and($freshBatch->stockMovements->first()->type)->toBe(StockMovementTypeEnum::In);
    });
});
