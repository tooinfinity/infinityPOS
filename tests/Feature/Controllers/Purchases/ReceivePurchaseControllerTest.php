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

it('may receive a pending purchase', function (): void {
    $product = Product::factory()->create(['created_by' => $this->user->id]);
    $purchase = Purchase::factory()->create([
        'status' => PurchaseStatusEnum::PENDING,
        'created_by' => $this->user->id,
    ]);
    PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'quantity' => 15,
    ]);

    $response = $this->post(route('purchases.receive', $purchase));

    $response->assertRedirect();

    $this->assertDatabaseHas('purchases', [
        'id' => $purchase->id,
        'status' => PurchaseStatusEnum::RECEIVED->value,
        'updated_by' => $this->user->id,
    ]);

    $this->assertDatabaseHas('stock_movements', [
        'source_type' => Purchase::class,
        'source_id' => $purchase->id,
        'quantity' => 15,
    ]);
});

it('cannot receive a cancelled purchase', function (): void {
    $purchase = Purchase::factory()->create([
        'status' => PurchaseStatusEnum::CANCELLED,
        'created_by' => $this->user->id,
    ]);

    $response = $this->post(route('purchases.receive', $purchase));

    $response->assertRedirect();
    $response->assertSessionHasErrors(['message']);
});

it('returns already received purchase without error', function (): void {
    $purchase = Purchase::factory()->create([
        'status' => PurchaseStatusEnum::RECEIVED,
        'created_by' => $this->user->id,
    ]);

    $response = $this->post(route('purchases.receive', $purchase));

    $response->assertRedirect();
    $response->assertSessionDoesntHaveErrors();
});
