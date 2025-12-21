<?php

declare(strict_types=1);

use App\Enums\SaleStatusEnum;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may cancel a pending sale', function (): void {
    $sale = Sale::factory()->create([
        'status' => SaleStatusEnum::PENDING,
        'created_by' => $this->user->id,
    ]);

    $response = $this->post(route('sales.cancel', $sale));

    $response->assertRedirect();

    $this->assertDatabaseHas('sales', [
        'id' => $sale->id,
        'status' => SaleStatusEnum::CANCELLED->value,
    ]);
});

it('may cancel a completed sale and reverse stock movements', function (): void {
    $product = Product::factory()->create(['created_by' => $this->user->id]);
    $sale = Sale::factory()->create([
        'status' => SaleStatusEnum::COMPLETED,
        'created_by' => $this->user->id,
    ]);
    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 10,
    ]);

    // Create initial stock movement (simulating completed sale)
    StockMovement::factory()->create([
        'product_id' => $product->id,
        'store_id' => $sale->store_id,
        'quantity' => -10,
        'source_type' => Sale::class,
        'source_id' => $sale->id,
        'created_by' => $this->user->id,
    ]);

    $response = $this->post(route('sales.cancel', $sale));

    $response->assertRedirect();

    $this->assertDatabaseHas('sales', [
        'id' => $sale->id,
        'status' => SaleStatusEnum::CANCELLED->value,
    ]);

    // Check reversal stock movement created
    $reversalMovement = StockMovement::query()
        ->where('source_type', Sale::class)
        ->where('source_id', $sale->id)
        ->where('quantity', 10)
        ->first();

    expect($reversalMovement)->not->toBeNull();
});

it('returns same sale if already cancelled', function (): void {
    $sale = Sale::factory()->create([
        'status' => SaleStatusEnum::CANCELLED,
        'created_by' => $this->user->id,
    ]);

    $response = $this->post(route('sales.cancel', $sale));

    $response->assertRedirect();

    $this->assertDatabaseHas('sales', [
        'id' => $sale->id,
        'status' => SaleStatusEnum::CANCELLED->value,
    ]);
});

it('requires authentication', function (): void {
    auth()->logout();

    $sale = Sale::factory()->create([
        'status' => SaleStatusEnum::PENDING,
    ]);

    $response = $this->post(route('sales.cancel', $sale));

    $response->assertStatus(302);
    $response->assertRedirect(route('login'));
});
