<?php

declare(strict_types=1);

use App\Actions\SaleReturn\DeleteSaleReturn;
use App\Enums\ReturnStatusEnum;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Unit;
use App\Models\Warehouse;

it('may delete a pending sale return', function (): void {
    $return = SaleReturn::factory()->create([
        'status' => ReturnStatusEnum::Pending,
    ]);

    $action = resolve(DeleteSaleReturn::class);

    $result = $action->handle($return);

    expect($result)->toBeTrue()
        ->and(SaleReturn::query()->where('id', $return->id)->exists())->toBeFalse();
});

it('throws exception when deleting non-pending return', function (): void {
    $return = SaleReturn::factory()->create([
        'status' => ReturnStatusEnum::Completed,
    ]);

    $action = resolve(DeleteSaleReturn::class);

    expect(fn () => $action->handle($return))->toThrow(App\Exceptions\InvalidOperationException::class);
});

it('throws exception when deleting completed return', function (): void {
    $return = SaleReturn::factory()->create([
        'status' => ReturnStatusEnum::Completed,
    ]);

    $action = resolve(DeleteSaleReturn::class);

    expect(fn () => $action->handle($return))
        ->toThrow(App\Exceptions\InvalidOperationException::class, 'Cannot delete SaleReturn. Only pending returns can be deleted');
});

it('deletes return with items', function (): void {
    $warehouse = Warehouse::factory()->create();
    $customer = Customer::factory()->create();
    $product = Product::factory()->for(Unit::factory()->create())->create();
    $sale = Sale::factory()->for($warehouse)->for($customer)->completed()->create();
    $sale->items()->create([
        'product_id' => $product->id,
        'batch_id' => null,
        'quantity' => 10,
        'unit_price' => 1000,
        'unit_cost' => 500,
        'subtotal' => 10000,
    ]);
    $return = SaleReturn::factory()->for($sale)->for($warehouse)->create([
        'status' => ReturnStatusEnum::Pending,
    ]);
    $return->items()->create([
        'product_id' => $product->id,
        'batch_id' => null,
        'quantity' => 5,
        'unit_price' => 1000,
        'subtotal' => 5000,
    ]);

    $action = resolve(DeleteSaleReturn::class);

    $result = $action->handle($return);

    expect($result)->toBeTrue()
        ->and($return->items()->count())->toBe(0)
        ->and(SaleReturn::query()->where('id', $return->id)->exists())->toBeFalse();
});
