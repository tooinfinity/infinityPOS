<?php

declare(strict_types=1);

use App\Enums\PurchaseReturnStatusEnum;
use App\Models\Product;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may complete a pending purchase return', function (): void {
    $product = Product::factory()->create(['created_by' => $this->user->id]);
    $purchaseReturn = PurchaseReturn::factory()->create([
        'status' => PurchaseReturnStatusEnum::PENDING,
        'created_by' => $this->user->id,
    ]);
    PurchaseReturnItem::factory()->create([
        'purchase_return_id' => $purchaseReturn->id,
        'product_id' => $product->id,
        'quantity' => 7,
    ]);

    $response = $this->post(route('purchase-returns.complete', $purchaseReturn));

    $response->assertRedirect();

    $this->assertDatabaseHas('purchase_returns', [
        'id' => $purchaseReturn->id,
        'status' => PurchaseReturnStatusEnum::COMPLETED->value,
        'updated_by' => $this->user->id,
    ]);

    $this->assertDatabaseHas('stock_movements', [
        'source_type' => PurchaseReturn::class,
        'source_id' => $purchaseReturn->id,
        'quantity' => -7,
    ]);
});

it('cannot complete a cancelled purchase return', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->create([
        'status' => PurchaseReturnStatusEnum::CANCELLED,
        'created_by' => $this->user->id,
    ]);

    $response = $this->post(route('purchase-returns.complete', $purchaseReturn));

    $response->assertRedirect();
    $response->assertSessionHasErrors(['message']);
});

it('returns already completed purchase return without error', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->create([
        'status' => PurchaseReturnStatusEnum::COMPLETED,
        'created_by' => $this->user->id,
    ]);

    $response = $this->post(route('purchase-returns.complete', $purchaseReturn));

    $response->assertRedirect();
    $response->assertSessionDoesntHaveErrors();
});
