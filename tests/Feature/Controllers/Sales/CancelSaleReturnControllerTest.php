<?php

declare(strict_types=1);

use App\Enums\SaleReturnStatusEnum;
use App\Models\Product;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\StockMovement;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may cancel a pending sale return', function (): void {
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

it('may cancel a completed sale return and reverse stock movements', function (): void {
    $product = Product::factory()->create(['created_by' => $this->user->id]);
    $saleReturn = SaleReturn::factory()->create([
        'status' => SaleReturnStatusEnum::COMPLETED,
        'created_by' => $this->user->id,
    ]);
    SaleReturnItem::factory()->create([
        'sale_return_id' => $saleReturn->id,
        'product_id' => $product->id,
        'quantity' => 5,
    ]);

    // Create initial stock movement (simulating completed return)
    StockMovement::factory()->create([
        'product_id' => $product->id,
        'store_id' => $saleReturn->store_id,
        'quantity' => 5,
        'source_type' => SaleReturn::class,
        'source_id' => $saleReturn->id,
        'created_by' => $this->user->id,
    ]);

    $response = $this->post(route('sale-returns.cancel', $saleReturn));

    $response->assertRedirect();

    $this->assertDatabaseHas('sale_returns', [
        'id' => $saleReturn->id,
        'status' => SaleReturnStatusEnum::CANCELLED->value,
    ]);

    // Check reversal stock movement created
    $reversalMovement = StockMovement::query()
        ->where('source_type', SaleReturn::class)
        ->where('source_id', $saleReturn->id)
        ->where('quantity', -5)
        ->first();

    expect($reversalMovement)->not->toBeNull();
});

it('returns same sale return if already cancelled', function (): void {
    $saleReturn = SaleReturn::factory()->create([
        'status' => SaleReturnStatusEnum::CANCELLED,
        'created_by' => $this->user->id,
    ]);

    $response = $this->post(route('sale-returns.cancel', $saleReturn));

    $response->assertRedirect();

    $this->assertDatabaseHas('sale_returns', [
        'id' => $saleReturn->id,
        'status' => SaleReturnStatusEnum::CANCELLED->value,
    ]);
});

it('requires authentication', function (): void {
    auth()->logout();

    $saleReturn = SaleReturn::factory()->create([
        'status' => SaleReturnStatusEnum::PENDING,
    ]);

    $response = $this->post(route('sale-returns.cancel', $saleReturn));

    $response->assertStatus(302);
    $response->assertRedirect(route('login'));
});
