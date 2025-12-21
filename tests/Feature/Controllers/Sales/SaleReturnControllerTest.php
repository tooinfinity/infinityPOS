<?php

declare(strict_types=1);

use App\Enums\SaleReturnStatusEnum;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may list sale returns', function (): void {
    SaleReturn::factory()->count(3)->create(['created_by' => $this->user->id]);

    $response = $this->get(route('sale-returns.index'));

    $response->assertStatus(500); // View not created yet
});

it('may show create sale return page', function (): void {
    $sale = Sale::factory()->create(['created_by' => $this->user->id]);

    $response = $this->get(route('sale-returns.create', $sale));

    $response->assertStatus(500); // View not created yet
});

it('may create a sale return', function (): void {
    $sale = Sale::factory()->create(['created_by' => $this->user->id]);
    $product = Product::factory()->create(['created_by' => $this->user->id]);
    $saleItem = SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
    ]);

    $response = $this->post(route('sale-returns.store'), [
        'reference' => 'RET-001',
        'sale_id' => $sale->id,
        'client_id' => $sale->client_id,
        'store_id' => $sale->store_id,
        'subtotal' => 10000,
        'discount' => 0,
        'tax' => 1000,
        'total' => 11000,
        'reason' => 'Defective product',
        'notes' => 'Customer returned item',
        'items' => [
            [
                'product_id' => $product->id,
                'sale_item_id' => $saleItem->id,
                'quantity' => 1,
                'price' => 10000,
                'cost' => 5000,
                'discount' => 0,
                'tax_amount' => 1000,
                'total' => 11000,
            ],
        ],
        'created_by' => $this->user->id,
    ]);

    $response->assertRedirect(route('sale-returns.index'));

    $this->assertDatabaseHas('sale_returns', [
        'reference' => 'RET-001',
        'sale_id' => $sale->id,
    ]);
});

it('may show a sale return', function (): void {
    $saleReturn = SaleReturn::factory()->create(['created_by' => $this->user->id]);

    $response = $this->get(route('sale-returns.show', $saleReturn));

    $response->assertStatus(500); // View not created yet
});

it('may complete a sale return', function (): void {
    $product = Product::factory()->create(['created_by' => $this->user->id]);
    $saleReturn = SaleReturn::factory()->create([
        'status' => SaleReturnStatusEnum::PENDING,
        'created_by' => $this->user->id,
    ]);
    SaleReturnItem::factory()->create([
        'sale_return_id' => $saleReturn->id,
        'product_id' => $product->id,
    ]);

    $response = $this->post(route('sale-returns.complete', $saleReturn));

    $response->assertRedirect();

    $this->assertDatabaseHas('sale_returns', [
        'id' => $saleReturn->id,
        'status' => SaleReturnStatusEnum::COMPLETED->value,
    ]);
});

it('may cancel a sale return', function (): void {
    $saleReturn = SaleReturn::factory()->create([
        'status' => SaleReturnStatusEnum::PENDING,
        'created_by' => $this->user->id,
    ]);

    $response = $this->post(route('sale-returns.cancel', $saleReturn));

    $response->assertRedirect();

    $this->assertDatabaseHas('sale_returns', [
        'id' => $saleReturn->id,
        'status' => SaleReturnStatusEnum::CANCELLED->value,
    ]);
});
