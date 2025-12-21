<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may create a sale item', function (): void {
    $sale = Sale::factory()->create(['created_by' => $this->user->id]);
    $product = Product::factory()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('sales.items.store', $sale), [
        'product_id' => $product->id,
        'quantity' => 5,
        'price' => 10000,
        'cost' => 5000,
        'discount' => 0,
        'tax_amount' => 500,
        'total' => 50500,
        'batch_number' => null,
        'expiry_date' => null,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('sale_items', [
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 5,
    ]);
});

it('may update a sale item', function (): void {
    $sale = Sale::factory()->create(['created_by' => $this->user->id]);
    $item = SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'quantity' => 5,
    ]);

    $response = $this->patch(route('sales.items.update', [$sale, $item]), [
        'quantity' => 10,
        'price' => null,
        'cost' => null,
        'discount' => null,
        'tax_amount' => null,
        'total' => null,
        'batch_number' => null,
        'expiry_date' => null,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('sale_items', [
        'id' => $item->id,
        'quantity' => 10,
    ]);
});

it('may delete a sale item', function (): void {
    $sale = Sale::factory()->create(['created_by' => $this->user->id]);
    $item = SaleItem::factory()->create(['sale_id' => $sale->id]);

    $response = $this->delete(route('sales.items.destroy', [$sale, $item]));

    $response->assertRedirect();

    $this->assertDatabaseMissing('sale_items', [
        'id' => $item->id,
    ]);
});

it('recalculates sale totals after adding item', function (): void {
    $sale = Sale::factory()->create([
        'created_by' => $this->user->id,
        'total' => 0,
    ]);
    $product = Product::factory()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('sales.items.store', $sale), [
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 10000,
        'cost' => 5000,
        'discount' => 0,
        'tax_amount' => 2000,
        'total' => 22000,
        'batch_number' => null,
        'expiry_date' => null,
    ]);

    $response->assertRedirect();

    $sale->refresh();
    expect($sale->total)->toBe(22000);
});

it('recalculates sale totals after updating item', function (): void {
    $sale = Sale::factory()->create([
        'created_by' => $this->user->id,
        'total' => 10000,
    ]);
    $item = SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'quantity' => 1,
        'price' => 10000,
        'total' => 10000,
    ]);

    $response = $this->patch(route('sales.items.update', [$sale, $item]), [
        'quantity' => 2,
        'price' => null,
        'cost' => null,
        'discount' => null,
        'tax_amount' => null,
        'total' => 20000,
        'batch_number' => null,
        'expiry_date' => null,
    ]);

    $response->assertRedirect();

    $sale->refresh();
    expect($sale->total)->toBe(20000);
});

it('recalculates sale totals after deleting item', function (): void {
    $sale = Sale::factory()->create([
        'created_by' => $this->user->id,
        'total' => 30000,
    ]);
    $item1 = SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'total' => 20000,
    ]);
    $item2 = SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'total' => 10000,
    ]);

    $response = $this->delete(route('sales.items.destroy', [$sale, $item2]));

    $response->assertRedirect();

    $sale->refresh();
    expect($sale->total)->toBe(20000);
});

it('may create item with batch number and expiry date', function (): void {
    $sale = Sale::factory()->create(['created_by' => $this->user->id]);
    $product = Product::factory()->create(['created_by' => $this->user->id]);

    $expiryDate = now()->addYear()->toDateString();

    $response = $this->post(route('sales.items.store', $sale), [
        'product_id' => $product->id,
        'quantity' => 5,
        'price' => 10000,
        'cost' => 5000,
        'discount' => 0,
        'tax_amount' => 500,
        'total' => 50500,
        'batch_number' => 'BATCH-001',
        'expiry_date' => $expiryDate,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('sale_items', [
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'batch_number' => 'BATCH-001',
    ]);
});

it('requires authentication for creating items', function (): void {
    auth()->logout();

    $sale = Sale::factory()->create();
    $product = Product::factory()->create();

    $response = $this->post(route('sales.items.store', $sale), [
        'product_id' => $product->id,
        'quantity' => 1,
        'price' => 10000,
        'cost' => 5000,
        'discount' => 0,
        'tax_amount' => 0,
        'total' => 10000,
    ]);

    $response->assertStatus(302);
    $response->assertRedirect(route('login'));
});

it('requires authentication for updating items', function (): void {
    auth()->logout();

    $sale = Sale::factory()->create();
    $item = SaleItem::factory()->create(['sale_id' => $sale->id]);

    $response = $this->patch(route('sales.items.update', [$sale, $item]), [
        'quantity' => 10,
    ]);

    $response->assertStatus(302);
    $response->assertRedirect(route('login'));
});

it('requires authentication for deleting items', function (): void {
    auth()->logout();

    $sale = Sale::factory()->create();
    $item = SaleItem::factory()->create(['sale_id' => $sale->id]);

    $response = $this->delete(route('sales.items.destroy', [$sale, $item]));

    $response->assertStatus(302);
    $response->assertRedirect(route('login'));
});
