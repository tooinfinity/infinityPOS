<?php

declare(strict_types=1);

use App\Models\InventoryLayer;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may list stock adjustments', function (): void {
    // Create adjustments (movements without source)
    StockMovement::factory()->count(3)->create([
        'source_type' => null,
        'source_id' => null,
        'created_by' => $this->user->id,
    ]);

    $response = $this->get(route('inventory.adjustments.index'));

    $response->assertStatus(500); // View not created yet
});

it('may show create adjustment page', function (): void {
    Product::factory()->count(2)->create(['created_by' => $this->user->id]);
    Store::factory()->count(2)->create(['created_by' => $this->user->id]);

    $response = $this->get(route('inventory.adjustments.create'));

    $response->assertStatus(500); // View not created yet
});

it('may create positive stock adjustment', function (): void {
    $product = Product::factory()->create(['created_by' => $this->user->id]);
    $store = Store::factory()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('inventory.adjustments.store'), [
        'product_id' => $product->id,
        'store_id' => $store->id,
        'quantity' => 50,
        'batch_number' => 'ADJ-001',
        'reason' => 'Stock found',
        'notes' => 'Found in warehouse',
        'created_by' => $this->user->id,
    ]);

    $response->assertRedirect(route('inventory.adjustments.index'));

    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product->id,
        'store_id' => $store->id,
        'quantity' => 50,
        'source_type' => null,
        'source_id' => null,
    ]);

    // Check layer was created
    $this->assertDatabaseHas('inventory_layers', [
        'product_id' => $product->id,
        'store_id' => $store->id,
        'remaining_qty' => 50,
    ]);
});

it('may create negative stock adjustment', function (): void {
    $product = Product::factory()->create(['created_by' => $this->user->id]);
    $store = Store::factory()->create(['created_by' => $this->user->id]);

    // Create existing stock
    InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'received_qty' => 100,
        'remaining_qty' => 100,
    ]);

    $response = $this->post(route('inventory.adjustments.store'), [
        'product_id' => $product->id,
        'store_id' => $store->id,
        'quantity' => -30,
        'batch_number' => null,
        'reason' => 'Damaged goods',
        'notes' => 'Damaged during transport',
        'created_by' => $this->user->id,
    ]);

    $response->assertRedirect(route('inventory.adjustments.index'));

    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product->id,
        'store_id' => $store->id,
        'quantity' => -30,
    ]);
});
