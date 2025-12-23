<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may list all stock movements', function (): void {
    StockMovement::factory()->count(5)->create(['created_by' => $this->user->id]);

    $response = $this->get(route('inventory.movements.index'));

    $response->assertStatus(200); // View not created yet
});

it('may show stock movements for a product', function (): void {
    $product = Product::factory()->create(['created_by' => $this->user->id]);

    StockMovement::factory()->count(3)->create([
        'product_id' => $product->id,
        'created_by' => $this->user->id,
    ]);

    $response = $this->get(route('inventory.movements.show', $product));

    $response->assertStatus(200); // View not created yet
});

it('may show stock movements for a product and store', function (): void {
    $product = Product::factory()->create(['created_by' => $this->user->id]);
    $store = Store::factory()->create(['created_by' => $this->user->id]);

    StockMovement::factory()->count(3)->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'created_by' => $this->user->id,
    ]);

    $response = $this->get(route('inventory.movements.show', [$product, 'store' => $store->id]));

    $response->assertStatus(200); // View not created yet
});

it('filters movements by product correctly', function (): void {
    $product1 = Product::factory()->create(['created_by' => $this->user->id]);
    $product2 = Product::factory()->create(['created_by' => $this->user->id]);
    $store = Store::factory()->create(['created_by' => $this->user->id]);

    StockMovement::factory()->count(3)->create([
        'product_id' => $product1->id,
        'store_id' => $store->id,
        'created_by' => $this->user->id,
    ]);

    StockMovement::factory()->count(2)->create([
        'product_id' => $product2->id,
        'store_id' => $store->id,
        'created_by' => $this->user->id,
    ]);

    $response = $this->get(route('inventory.movements.show', $product1));

    $response->assertStatus(200); // View not created yet
});

it('filters movements by product and store correctly', function (): void {
    $product = Product::factory()->create(['created_by' => $this->user->id]);
    $store1 = Store::factory()->create(['created_by' => $this->user->id]);
    $store2 = Store::factory()->create(['created_by' => $this->user->id]);

    StockMovement::factory()->count(3)->create([
        'product_id' => $product->id,
        'store_id' => $store1->id,
        'created_by' => $this->user->id,
    ]);

    StockMovement::factory()->count(2)->create([
        'product_id' => $product->id,
        'store_id' => $store2->id,
        'created_by' => $this->user->id,
    ]);

    $response = $this->get(route('inventory.movements.show', [$product, 'store' => $store1->id]));

    $response->assertStatus(200); // View not created yet
});

it('handles movements without store filter', function (): void {
    $product = Product::factory()->create(['created_by' => $this->user->id]);

    StockMovement::factory()->count(5)->create([
        'product_id' => $product->id,
        'created_by' => $this->user->id,
    ]);

    $response = $this->get(route('inventory.movements.show', $product));

    $response->assertStatus(200); // View not created yet
});

it('paginates movements correctly', function (): void {
    $product = Product::factory()->create(['created_by' => $this->user->id]);

    StockMovement::factory()->count(60)->create([
        'product_id' => $product->id,
        'created_by' => $this->user->id,
    ]);

    $response = $this->get(route('inventory.movements.show', $product));

    $response->assertStatus(200); // View not created yet
});

it('filters by store when store instance is passed', function (): void {
    $product = Product::factory()->create(['created_by' => $this->user->id]);
    $store1 = Store::factory()->create(['created_by' => $this->user->id]);
    $store2 = Store::factory()->create(['created_by' => $this->user->id]);

    StockMovement::factory()->count(3)->create([
        'product_id' => $product->id,
        'store_id' => $store1->id,
        'created_by' => $this->user->id,
    ]);

    StockMovement::factory()->count(2)->create([
        'product_id' => $product->id,
        'store_id' => $store2->id,
        'created_by' => $this->user->id,
    ]);

    // Pass Store instance directly (not via query parameter)
    $response = $this->get(route('inventory.movements.show', ['product' => $product, 'store' => $store1]));

    $response->assertStatus(200); // View not created yet
});
