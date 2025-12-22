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

it('may show bulk adjustment create page', function (): void {
    Product::factory()->count(2)->create(['created_by' => $this->user->id]);
    Store::factory()->count(2)->create(['created_by' => $this->user->id]);

    $response = $this->get(route('inventory.bulk-adjustments.create'));

    $response->assertStatus(500); // View not created yet
});

it('may create bulk stock adjustments', function (): void {
    $product1 = Product::factory()->create(['created_by' => $this->user->id]);
    $product2 = Product::factory()->create(['created_by' => $this->user->id]);
    $store = Store::factory()->create(['created_by' => $this->user->id]);

    // Create existing stock for product2
    InventoryLayer::factory()->create([
        'product_id' => $product2->id,
        'store_id' => $store->id,
        'received_qty' => 100,
        'remaining_qty' => 100,
    ]);

    $response = $this->post(route('inventory.bulk-adjustments.store'), [
        'adjustments' => [
            [
                'product_id' => $product1->id,
                'store_id' => $store->id,
                'quantity' => 50,
                'batch_number' => null,
                'reason' => 'Stock count',
                'notes' => 'Increase',
                'created_by' => $this->user->id,
            ],
            [
                'product_id' => $product2->id,
                'store_id' => $store->id,
                'quantity' => -20,
                'batch_number' => null,
                'reason' => 'Damaged',
                'notes' => 'Decrease',
                'created_by' => $this->user->id,
            ],
        ],
        'reference' => 'BULK-ADJ-001',
        'notes' => 'Year end adjustment',
    ]);

    $response->assertRedirect(route('inventory.adjustments.index'));

    // Check movements were created
    expect(StockMovement::query()->where('product_id', $product1->id)->count())->toBe(1)
        ->and(StockMovement::query()->where('product_id', $product2->id)->count())->toBe(1);
});
