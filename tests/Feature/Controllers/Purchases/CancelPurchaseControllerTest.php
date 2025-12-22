<?php

declare(strict_types=1);

use App\Enums\PurchaseStatusEnum;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may cancel a pending purchase', function (): void {
    $purchase = Purchase::factory()->create([
        'status' => PurchaseStatusEnum::PENDING,
        'created_by' => $this->user->id,
    ]);

    $response = $this->post(route('purchases.cancel', $purchase));

    $response->assertRedirect();

    $this->assertDatabaseHas('purchases', [
        'id' => $purchase->id,
        'status' => PurchaseStatusEnum::CANCELLED->value,
        'updated_by' => $this->user->id,
    ]);
});

it('may cancel a received purchase and reverse stock', function (): void {
    $product = Product::factory()->create(['created_by' => $this->user->id]);
    $purchase = Purchase::factory()->create([
        'status' => PurchaseStatusEnum::RECEIVED,
        'created_by' => $this->user->id,
    ]);
    PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'quantity' => 20,
    ]);

    $response = $this->post(route('purchases.cancel', $purchase));

    $response->assertRedirect();

    $this->assertDatabaseHas('purchases', [
        'id' => $purchase->id,
        'status' => PurchaseStatusEnum::CANCELLED->value,
    ]);

    $this->assertDatabaseHas('stock_movements', [
        'source_type' => Purchase::class,
        'source_id' => $purchase->id,
        'quantity' => -20,
    ]);
});

it('returns already cancelled purchase without error', function (): void {
    $purchase = Purchase::factory()->create([
        'status' => PurchaseStatusEnum::CANCELLED,
        'created_by' => $this->user->id,
    ]);

    $response = $this->post(route('purchases.cancel', $purchase));

    $response->assertRedirect();
    $response->assertSessionDoesntHaveErrors();
});
