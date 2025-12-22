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

it('may cancel a pending purchase return', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->create([
        'status' => PurchaseReturnStatusEnum::PENDING,
        'created_by' => $this->user->id,
    ]);

    $response = $this->post(route('purchase-returns.cancel', $purchaseReturn));

    $response->assertRedirect();

    $this->assertDatabaseHas('purchase_returns', [
        'id' => $purchaseReturn->id,
        'status' => PurchaseReturnStatusEnum::CANCELLED->value,
        'updated_by' => $this->user->id,
    ]);
});

it('may cancel a completed purchase return and reverse stock', function (): void {
    $product = Product::factory()->create(['created_by' => $this->user->id]);
    $purchaseReturn = PurchaseReturn::factory()->create([
        'status' => PurchaseReturnStatusEnum::COMPLETED,
        'created_by' => $this->user->id,
    ]);
    PurchaseReturnItem::factory()->create([
        'purchase_return_id' => $purchaseReturn->id,
        'product_id' => $product->id,
        'quantity' => 4,
    ]);

    $response = $this->post(route('purchase-returns.cancel', $purchaseReturn));

    $response->assertRedirect();

    $this->assertDatabaseHas('purchase_returns', [
        'id' => $purchaseReturn->id,
        'status' => PurchaseReturnStatusEnum::CANCELLED->value,
    ]);

    $this->assertDatabaseHas('stock_movements', [
        'source_type' => PurchaseReturn::class,
        'source_id' => $purchaseReturn->id,
        'quantity' => 4,
    ]);
});

it('returns already cancelled purchase return without error', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->create([
        'status' => PurchaseReturnStatusEnum::CANCELLED,
        'created_by' => $this->user->id,
    ]);

    $response = $this->post(route('purchase-returns.cancel', $purchaseReturn));

    $response->assertRedirect();
    $response->assertSessionDoesntHaveErrors();
});
