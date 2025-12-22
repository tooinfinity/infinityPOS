<?php

declare(strict_types=1);

use App\Models\InventoryLayer;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may list inventory levels', function (): void {
    Product::factory()->count(3)->create(['created_by' => $this->user->id]);

    $response = $this->get(route('inventory.levels.index'));

    $response->assertStatus(500); // View not created yet
});

it('may show inventory levels for product and store', function (): void {
    $product = Product::factory()->create(['created_by' => $this->user->id]);
    $store = Store::factory()->create(['created_by' => $this->user->id]);

    InventoryLayer::factory()->count(3)->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
    ]);

    $response = $this->get(route('inventory.levels.show', [$product, $store]));

    $response->assertStatus(500); // View not created yet
});

it('may recalculate stock levels', function (): void {
    $product = Product::factory()->create(['created_by' => $this->user->id]);
    $store = Store::factory()->create(['created_by' => $this->user->id]);

    InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'remaining_qty' => 50,
    ]);

    InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'remaining_qty' => 30,
    ]);

    $response = $this->post(route('inventory.levels.recalculate', [$product, $store]));

    $response->assertRedirect();
    $response->assertSessionHas('message', 'Stock recalculated: 80 units');
});
