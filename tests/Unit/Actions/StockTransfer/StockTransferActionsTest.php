<?php

declare(strict_types=1);

use App\Actions\StockTransfer\CancelStockTransfer;
use App\Actions\StockTransfer\DeleteStockTransfer;
use App\Models\Batch;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\Unit;
use App\Models\Warehouse;

describe(DeleteStockTransfer::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->product = Product::factory()->for($this->unit)->create();
        $this->fromWarehouse = Warehouse::factory()->create();
        $this->toWarehouse = Warehouse::factory()->create();
    });

    it('may delete a pending stock transfer', function (): void {
        $transfer = StockTransfer::factory()->for($this->fromWarehouse, 'fromWarehouse')->for($this->toWarehouse, 'toWarehouse')->pending()->create();

        $action = resolve(DeleteStockTransfer::class);

        $result = $action->handle($transfer);

        expect($result)->toBeTrue()
            ->and(StockTransfer::query()->where('id', $transfer->id)->exists())->toBeFalse();
    });

    it('throws exception when deleting completed transfer', function (): void {
        $transfer = StockTransfer::factory()->for($this->fromWarehouse, 'fromWarehouse')->for($this->toWarehouse, 'toWarehouse')->completed()->create();

        $action = resolve(DeleteStockTransfer::class);

        expect(fn () => $action->handle($transfer))->toThrow(App\Exceptions\InvalidOperationException::class);
    });

    it('throws exception when deleting cancelled transfer', function (): void {
        $transfer = StockTransfer::factory()->for($this->fromWarehouse, 'fromWarehouse')->for($this->toWarehouse, 'toWarehouse')->cancelled()->create();

        $action = resolve(DeleteStockTransfer::class);

        expect(fn () => $action->handle($transfer))->toThrow(App\Exceptions\InvalidOperationException::class);
    });
});

describe(CancelStockTransfer::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->product = Product::factory()->for($this->unit)->create();
        $this->batch = Batch::factory()->for($this->product)->create(['quantity' => 50]);
        $this->fromWarehouse = Warehouse::factory()->create();
        $this->toWarehouse = Warehouse::factory()->create();
    });

    it('may cancel a pending stock transfer', function (): void {
        $transfer = StockTransfer::factory()->for($this->fromWarehouse, 'fromWarehouse')->for($this->toWarehouse, 'toWarehouse')->pending()->create();

        $action = resolve(CancelStockTransfer::class);

        $result = $action->handle($transfer);

        expect($result->status)->toBe(App\Enums\StockTransferStatusEnum::Cancelled);
    });

    it('throws exception when cancelling completed transfer', function (): void {
        $transfer = StockTransfer::factory()->for($this->fromWarehouse, 'fromWarehouse')->for($this->toWarehouse, 'toWarehouse')->completed()->create();

        $action = resolve(CancelStockTransfer::class);

        expect(fn () => $action->handle($transfer))->toThrow(App\Exceptions\StateTransitionException::class);
    });

    it('throws exception when cancelling already cancelled transfer', function (): void {
        $transfer = StockTransfer::factory()->for($this->fromWarehouse, 'fromWarehouse')->for($this->toWarehouse, 'toWarehouse')->cancelled()->create();

        $action = resolve(CancelStockTransfer::class);

        expect(fn () => $action->handle($transfer))->toThrow(App\Exceptions\StateTransitionException::class);
    });
});
