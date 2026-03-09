<?php

declare(strict_types=1);

use App\Actions\SaleReturn\CompleteSaleReturn;
use App\Actions\SaleReturn\DeleteSaleReturn;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Unit;
use App\Models\Warehouse;

describe(DeleteSaleReturn::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->product = Product::factory()->for($this->unit)->create();
        $this->warehouse = Warehouse::factory()->create();
        $this->customer = Customer::factory()->create();
    });

    it('may delete a pending sale return', function (): void {
        $sale = Sale::factory()->for($this->warehouse)->for($this->customer)->create();
        $saleReturn = SaleReturn::factory()->for($sale)->pending()->create();

        $action = resolve(DeleteSaleReturn::class);

        $result = $action->handle($saleReturn);

        expect($result)->toBeTrue()
            ->and(SaleReturn::query()->where('id', $saleReturn->id)->exists())->toBeFalse();
    });

    it('throws exception when deleting completed sale return', function (): void {
        $sale = Sale::factory()->for($this->warehouse)->for($this->customer)->create();
        $saleReturn = SaleReturn::factory()->for($sale)->completed()->create();

        $action = resolve(DeleteSaleReturn::class);

        expect(fn () => $action->handle($saleReturn))->toThrow(App\Exceptions\InvalidOperationException::class);
    });
});

describe(CompleteSaleReturn::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->product = Product::factory()->for($this->unit)->create();
        $this->warehouse = Warehouse::factory()->create();
        $this->customer = Customer::factory()->create();
    });

    it('throws exception when completing already completed sale return', function (): void {
        $sale = Sale::factory()->for($this->warehouse)->for($this->customer)->create();
        $saleReturn = SaleReturn::factory()->for($sale)->completed()->create();

        $action = resolve(CompleteSaleReturn::class);

        expect(fn () => $action->handle($saleReturn))->toThrow(App\Exceptions\StateTransitionException::class);
    });
});
