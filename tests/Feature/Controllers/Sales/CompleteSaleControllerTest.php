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

it('may complete a pending sale', function (): void {
    $product = Product::factory()->create(['created_by' => $this->user->id]);
    $sale = Sale::factory()->create([
        'status' => SaleStatusEnum::PENDING,
        'created_by' => $this->user->id,
    ]);
    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 10,
    ]);

    $response = $this->post(route('sales.complete', $sale));

    $response->assertRedirect();

    $this->assertDatabaseHas('sales', [
        'id' => $sale->id,
        'status' => SaleStatusEnum::COMPLETED->value,
    ]);

    // Verify stock movement created
    $stockMovement = StockMovement::query()
        ->where('source_type', Sale::class)
        ->where('source_id', $sale->id)
        ->first();

    expect($stockMovement)->not->toBeNull()
        ->and($stockMovement->quantity)->toBe(-10)
        ->and($stockMovement->product_id)->toBe($product->id);
});

it('cannot complete a cancelled sale', function (): void {
    $sale = Sale::factory()->create([
        'status' => SaleStatusEnum::CANCELLED,
        'created_by' => $this->user->id,
    ]);

    $response = $this->post(route('sales.complete', $sale));

    $response->assertRedirect();
    $response->assertSessionHasErrors(['message']);

    $this->assertDatabaseHas('sales', [
        'id' => $sale->id,
        'status' => SaleStatusEnum::CANCELLED->value,
    ]);
});

it('returns same sale if already completed', function (): void {
    $product = Product::factory()->create(['created_by' => $this->user->id]);
    $sale = Sale::factory()->create([
        'status' => SaleStatusEnum::COMPLETED,
        'created_by' => $this->user->id,
    ]);
    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
    ]);

    $initialMovementsCount = StockMovement::query()
        ->where('source_type', Sale::class)
        ->where('source_id', $sale->id)
        ->count();

    $response = $this->post(route('sales.complete', $sale));

    $response->assertRedirect();

    // Should not create duplicate stock movements
    $finalMovementsCount = StockMovement::query()
        ->where('source_type', Sale::class)
        ->where('source_id', $sale->id)
        ->count();

    expect($finalMovementsCount)->toBe($initialMovementsCount);
});

it('requires authentication', function (): void {
    auth()->logout();

    $sale = Sale::factory()->create([
        'status' => SaleStatusEnum::PENDING,
    ]);

    $response = $this->post(route('sales.complete', $sale));

    $response->assertStatus(302);
    $response->assertRedirect(route('login'));
});
