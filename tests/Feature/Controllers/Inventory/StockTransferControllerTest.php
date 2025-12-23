<?php

declare(strict_types=1);

use App\Enums\StockTransferStatusEnum;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\Store;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may list stock transfers', function (): void {
    StockTransfer::factory()->count(3)->create(['created_by' => $this->user->id]);

    $response = $this->get(route('inventory.stock-transfers.index'));

    $response->assertStatus(200); // View not created yet
});

it('may show create stock transfer page', function (): void {
    Store::factory()->count(2)->create(['created_by' => $this->user->id]);
    Product::factory()->count(2)->create(['created_by' => $this->user->id]);

    $response = $this->get(route('inventory.stock-transfers.create'));

    $response->assertStatus(200); // View not created yet
});

it('may create a stock transfer', function (): void {
    $fromStore = Store::factory()->create(['created_by' => $this->user->id]);
    $toStore = Store::factory()->create(['created_by' => $this->user->id]);
    $product = Product::factory()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('inventory.stock-transfers.store'), [
        'reference' => 'TR-001',
        'from_store_id' => $fromStore->id,
        'to_store_id' => $toStore->id,
        'notes' => 'Test transfer',
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 20,
                'batch_number' => null,
            ],
        ],
        'created_by' => $this->user->id,
    ]);

    $response->assertRedirect(route('inventory.stock-transfers.index'));

    $this->assertDatabaseHas('stock_transfers', [
        'reference' => 'TR-001',
        'from_store_id' => $fromStore->id,
        'to_store_id' => $toStore->id,
        'status' => StockTransferStatusEnum::PENDING->value,
    ]);
});

it('may show a stock transfer', function (): void {
    $transfer = StockTransfer::factory()->create(['created_by' => $this->user->id]);

    $response = $this->get(route('inventory.stock-transfers.show', $transfer));

    $response->assertStatus(200); // View not created yet
});
