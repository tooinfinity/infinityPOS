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

it('may complete a pending sale return', function (): void {
    $product = Product::factory()->create(['created_by' => $this->user->id]);
    $saleReturn = SaleReturn::factory()->create([
        'status' => SaleReturnStatusEnum::PENDING,
        'created_by' => $this->user->id,
    ]);
    SaleReturnItem::factory()->create([
        'sale_return_id' => $saleReturn->id,
        'product_id' => $product->id,
        'quantity' => 5,
    ]);

    $response = $this->post(route('sale-returns.complete', $saleReturn));

    $response->assertRedirect();

    $this->assertDatabaseHas('sale_returns', [
        'id' => $saleReturn->id,
        'status' => SaleReturnStatusEnum::COMPLETED->value,
    ]);

    // Verify stock movement created (positive quantity for returns)
    $stockMovement = StockMovement::query()
        ->where('source_type', SaleReturn::class)
        ->where('source_id', $saleReturn->id)
        ->first();

    expect($stockMovement)->not->toBeNull()
        ->and($stockMovement->quantity)->toBe(5)
        ->and($stockMovement->product_id)->toBe($product->id);
});

it('cannot complete a cancelled sale return', function (): void {
    $saleReturn = SaleReturn::factory()->create([
        'status' => SaleReturnStatusEnum::CANCELLED,
        'created_by' => $this->user->id,
    ]);

    $response = $this->post(route('sale-returns.complete', $saleReturn));

    $response->assertRedirect();
    $response->assertSessionHasErrors(['message']);

    $this->assertDatabaseHas('sale_returns', [
        'id' => $saleReturn->id,
        'status' => SaleReturnStatusEnum::CANCELLED->value,
    ]);
});

it('returns same sale return if already completed', function (): void {
    $product = Product::factory()->create(['created_by' => $this->user->id]);
    $saleReturn = SaleReturn::factory()->create([
        'status' => SaleReturnStatusEnum::COMPLETED,
        'created_by' => $this->user->id,
    ]);
    SaleReturnItem::factory()->create([
        'sale_return_id' => $saleReturn->id,
        'product_id' => $product->id,
    ]);

    $initialMovementsCount = StockMovement::query()
        ->where('source_type', SaleReturn::class)
        ->where('source_id', $saleReturn->id)
        ->count();

    $response = $this->post(route('sale-returns.complete', $saleReturn));

    $response->assertRedirect();

    // Should not create duplicate stock movements
    $finalMovementsCount = StockMovement::query()
        ->where('source_type', SaleReturn::class)
        ->where('source_id', $saleReturn->id)
        ->count();

    expect($finalMovementsCount)->toBe($initialMovementsCount);
});

it('requires authentication', function (): void {
    auth()->logout();

    $saleReturn = SaleReturn::factory()->create([
        'status' => SaleReturnStatusEnum::PENDING,
    ]);

    $response = $this->post(route('sale-returns.complete', $saleReturn));

    $response->assertStatus(302);
    $response->assertRedirect(route('login'));
});
