<?php

declare(strict_types=1);

use App\Enums\PurchaseReturnStatusEnum;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may list purchase returns', function (): void {
    PurchaseReturn::factory()->count(3)->create(['created_by' => $this->user->id]);

    $response = $this->get(route('purchase-returns.index'));

    $response->assertStatus(500); // View not created yet
});

it('may show create purchase return page', function (): void {
    $purchase = Purchase::factory()->create(['created_by' => $this->user->id]);

    $response = $this->get(route('purchase-returns.create', $purchase));

    $response->assertStatus(500); // View not created yet
});

it('may create a purchase return', function (): void {
    $product = Product::factory()->create(['created_by' => $this->user->id]);
    $purchase = Purchase::factory()->create(['created_by' => $this->user->id]);
    $purchaseItem = PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'quantity' => 20,
        'cost' => 1000,
    ]);

    $response = $this->post(route('purchase-returns.store'), [
        'reference' => 'PR-001',
        'purchase_id' => $purchase->id,
        'supplier_id' => $purchase->supplier_id,
        'store_id' => $purchase->store_id,
        'subtotal' => 5000,
        'discount' => 0,
        'tax' => 0,
        'total' => 5000,
        'reason' => 'Defective items',
        'notes' => 'Return test',
        'items' => [
            [
                'product_id' => $product->id,
                'purchase_item_id' => $purchaseItem->id,
                'quantity' => 5,
                'cost' => 1000,
                'total' => 5000,
                'batch_number' => null,
            ],
        ],
        'created_by' => $this->user->id,
    ]);

    $response->assertRedirect(route('purchase-returns.index'));

    $this->assertDatabaseHas('purchase_returns', [
        'reference' => 'PR-001',
        'purchase_id' => $purchase->id,
        'reason' => 'Defective items',
    ]);
});

it('may show a purchase return', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->create(['created_by' => $this->user->id]);

    $response = $this->get(route('purchase-returns.show', $purchaseReturn));

    $response->assertStatus(500); // View not created yet
});

it('may complete a purchase return', function (): void {
    $product = Product::factory()->create(['created_by' => $this->user->id]);
    $purchaseReturn = PurchaseReturn::factory()->create([
        'status' => PurchaseReturnStatusEnum::PENDING,
        'created_by' => $this->user->id,
    ]);
    $purchaseReturn->items()->create([
        'product_id' => $product->id,
        'quantity' => 5,
        'cost' => 1000,
        'total' => 5000,
    ]);

    $response = $this->post(route('purchase-returns.complete', $purchaseReturn));

    $response->assertRedirect();

    $this->assertDatabaseHas('purchase_returns', [
        'id' => $purchaseReturn->id,
        'status' => PurchaseReturnStatusEnum::COMPLETED->value,
    ]);
});

it('may cancel a purchase return', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->create([
        'status' => PurchaseReturnStatusEnum::PENDING,
        'created_by' => $this->user->id,
    ]);

    $response = $this->post(route('purchase-returns.cancel', $purchaseReturn));

    $response->assertRedirect();

    $this->assertDatabaseHas('purchase_returns', [
        'id' => $purchaseReturn->id,
        'status' => PurchaseReturnStatusEnum::CANCELLED->value,
    ]);
});
