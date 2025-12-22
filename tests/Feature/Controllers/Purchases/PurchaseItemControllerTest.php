<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may add item to purchase', function (): void {
    $purchase = Purchase::factory()->create(['created_by' => $this->user->id]);
    $product = Product::factory()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('purchases.items.store', $purchase), [
        'product_id' => $product->id,
        'quantity' => 20,
        'cost' => 2000,
        'discount' => 200,
        'tax_amount' => 400,
        'total' => 40200,
        'batch_number' => 'BATCH-TEST',
        'expiry_date' => now()->addMonths(6)->toDateString(),
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('purchase_items', [
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'quantity' => 20,
        'cost' => 2000,
    ]);
});

it('may update purchase item', function (): void {
    $purchase = Purchase::factory()->create(['created_by' => $this->user->id]);
    $item = PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'quantity' => 10,
        'cost' => 1000,
    ]);

    $response = $this->patch(route('purchases.items.update', [$purchase, $item]), [
        'quantity' => 25,
        'cost' => 1500,
        'discount' => null,
        'tax_amount' => null,
        'total' => null,
        'batch_number' => null,
        'expiry_date' => null,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('purchase_items', [
        'id' => $item->id,
        'quantity' => 25,
        'cost' => 1500,
    ]);
});

it('may delete purchase item', function (): void {
    $purchase = Purchase::factory()->create(['created_by' => $this->user->id]);
    $item = PurchaseItem::factory()->create(['purchase_id' => $purchase->id]);

    $response = $this->delete(route('purchases.items.destroy', [$purchase, $item]));

    $response->assertRedirect();

    $this->assertDatabaseMissing('purchase_items', [
        'id' => $item->id,
    ]);
});

it('recalculates purchase totals after adding item', function (): void {
    $purchase = Purchase::factory()->create([
        'subtotal' => 0,
        'tax' => 0,
        'total' => 0,
        'created_by' => $this->user->id,
    ]);
    $product = Product::factory()->create(['created_by' => $this->user->id]);

    $this->post(route('purchases.items.store', $purchase), [
        'product_id' => $product->id,
        'quantity' => 10,
        'cost' => 1000,
        'discount' => 500,
        'tax_amount' => 500,
        'total' => 10000,
        'batch_number' => null,
        'expiry_date' => null,
    ]);

    $purchase->refresh();

    expect($purchase->subtotal)->toBe(9500)
        ->and($purchase->tax)->toBe(500)
        ->and($purchase->total)->toBe(10000);
});
