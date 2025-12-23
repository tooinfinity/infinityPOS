<?php

declare(strict_types=1);

use App\Enums\PurchaseStatusEnum;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may list purchases', function (): void {
    Purchase::factory()->count(3)->create(['created_by' => $this->user->id]);

    $response = $this->get(route('purchases.index'));

    $response->assertStatus(200); // View not created yet
});

it('may show create purchase page', function (): void {
    Supplier::factory()->count(2)->create(['created_by' => $this->user->id]);
    Store::factory()->count(2)->create(['created_by' => $this->user->id]);

    $response = $this->get(route('purchases.create'));

    $response->assertStatus(200); // View not created yet
});

it('may create a purchase', function (): void {
    $supplier = Supplier::factory()->create(['created_by' => $this->user->id]);
    $store = Store::factory()->create(['created_by' => $this->user->id]);
    $product = Product::factory()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('purchases.store'), [
        'reference' => 'PO-001',
        'supplier_id' => $supplier->id,
        'store_id' => $store->id,
        'subtotal' => 10000,
        'discount' => 0,
        'tax' => 1000,
        'total' => 11000,
        'notes' => 'Test purchase',
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 10,
                'cost' => 1000,
                'discount' => 0,
                'tax_amount' => 1000,
                'total' => 11000,
                'batch_number' => 'BATCH-001',
                'expiry_date' => now()->addYear()->toDateString(),
            ],
        ],
        'created_by' => $this->user->id,
    ]);

    $response->assertRedirect(route('purchases.index'));

    $this->assertDatabaseHas('purchases', [
        'reference' => 'PO-001',
        'store_id' => $store->id,
    ]);
});

it('may show a purchase', function (): void {
    $purchase = Purchase::factory()->create(['created_by' => $this->user->id]);

    $response = $this->get(route('purchases.show', $purchase));

    $response->assertStatus(200); // View not created yet
});

it('may show edit purchase page', function (): void {
    $purchase = Purchase::factory()->create(['created_by' => $this->user->id]);

    $response = $this->get(route('purchases.edit', $purchase));

    $response->assertStatus(200); // View not created yet
});

it('may update a purchase', function (): void {
    $purchase = Purchase::factory()->create([
        'reference' => 'PO-001',
        'created_by' => $this->user->id,
    ]);

    $response = $this->patch(route('purchases.update', $purchase), [
        'reference' => 'PO-UPDATED',
        'supplier_id' => null,
        'store_id' => null,
        'subtotal' => null,
        'discount' => null,
        'tax' => null,
        'total' => null,
        'notes' => 'Updated notes',
        'updated_by' => $this->user->id,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('purchases', [
        'id' => $purchase->id,
        'reference' => 'PO-UPDATED',
        'notes' => 'Updated notes',
    ]);
});

it('may delete a pending purchase', function (): void {
    $purchase = Purchase::factory()->create([
        'status' => PurchaseStatusEnum::PENDING,
        'created_by' => $this->user->id,
    ]);

    $response = $this->delete(route('purchases.destroy', $purchase));

    $response->assertRedirect(route('purchases.index'));

    $this->assertDatabaseMissing('purchases', [
        'id' => $purchase->id,
    ]);
});

it('cannot delete a received purchase', function (): void {
    $purchase = Purchase::factory()->create([
        'status' => PurchaseStatusEnum::RECEIVED,
        'created_by' => $this->user->id,
    ]);

    $response = $this->delete(route('purchases.destroy', $purchase));

    $response->assertRedirect();
    $response->assertSessionHasErrors(['message']);

    $this->assertDatabaseHas('purchases', [
        'id' => $purchase->id,
    ]);
});

it('may receive a purchase', function (): void {
    $product = Product::factory()->create(['created_by' => $this->user->id]);
    $purchase = Purchase::factory()->create([
        'status' => PurchaseStatusEnum::PENDING,
        'created_by' => $this->user->id,
    ]);
    PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
    ]);

    $response = $this->post(route('purchases.receive', $purchase));

    $response->assertRedirect();

    $this->assertDatabaseHas('purchases', [
        'id' => $purchase->id,
        'status' => PurchaseStatusEnum::RECEIVED->value,
    ]);
});

it('may cancel a purchase', function (): void {
    $purchase = Purchase::factory()->create([
        'status' => PurchaseStatusEnum::PENDING,
        'created_by' => $this->user->id,
    ]);

    $response = $this->post(route('purchases.cancel', $purchase));

    $response->assertRedirect();

    $this->assertDatabaseHas('purchases', [
        'id' => $purchase->id,
        'status' => PurchaseStatusEnum::CANCELLED->value,
    ]);
});
