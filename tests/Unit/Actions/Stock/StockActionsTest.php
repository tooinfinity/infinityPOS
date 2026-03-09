<?php

declare(strict_types=1);

use App\Actions\Stock\AddStock;
use App\Actions\Stock\AdjustStock;
use App\Actions\Stock\DeductStock;
use App\Actions\Stock\RecordStockMovement;
use App\Models\Batch;
use App\Models\Product;
use App\Models\Sale;
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

    it('records stock movement when deducting stock', function (): void {
        $action = resolve(DeductStock::class);
        $sale = Sale::factory()->for($this->warehouse)->create();

        $action->handle($this->batch, 10, $sale);

        expect($this->batch->stockMovements()->count())->toBe(1);
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
            App\Enums\StockMovementTypeEnum::In,
            10,
            $sale,
            40,
            'Test movement'
        );

        expect($result)->toBeInstanceOf(App\Models\StockMovement::class);
        expect($this->batch->stockMovements()->count())->toBe(1);
    });
});
