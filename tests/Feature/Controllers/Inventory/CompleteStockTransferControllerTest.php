<?php

declare(strict_types=1);

use App\Enums\StockTransferStatusEnum;
use App\Models\InventoryLayer;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may complete a pending stock transfer', function (): void {
    $product = Product::factory()->create(['created_by' => $this->user->id]);
    $transfer = StockTransfer::factory()->pending()->create(['created_by' => $this->user->id]);

    // Create stock in source store
    InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $transfer->from_store_id,
        'received_qty' => 100,
        'remaining_qty' => 100,
    ]);

    $transfer->items()->create([
        'product_id' => $product->id,
        'quantity' => 30,
        'batch_number' => null,
    ]);

    $response = $this->post(route('inventory.stock-transfers.complete', $transfer));

    $response->assertRedirect();

    $this->assertDatabaseHas('stock_transfers', [
        'id' => $transfer->id,
        'status' => StockTransferStatusEnum::COMPLETED->value,
        'updated_by' => $this->user->id,
    ]);
});

it('cannot complete a cancelled transfer', function (): void {
    $transfer = StockTransfer::factory()->cancelled()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('inventory.stock-transfers.complete', $transfer));

    $response->assertRedirect();
    $response->assertSessionHasErrors(['message']);
});

it('returns already completed transfer without error', function (): void {
    $transfer = StockTransfer::factory()->completed()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('inventory.stock-transfers.complete', $transfer));

    $response->assertRedirect();
    $response->assertSessionDoesntHaveErrors();
});
