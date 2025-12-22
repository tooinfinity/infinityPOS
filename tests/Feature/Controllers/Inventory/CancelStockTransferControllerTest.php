<?php

declare(strict_types=1);

use App\Enums\StockTransferStatusEnum;
use App\Models\StockTransfer;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may cancel a pending stock transfer', function (): void {
    $transfer = StockTransfer::factory()->pending()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('inventory.stock-transfers.cancel', $transfer));

    $response->assertRedirect();

    $this->assertDatabaseHas('stock_transfers', [
        'id' => $transfer->id,
        'status' => StockTransferStatusEnum::CANCELLED->value,
        'updated_by' => $this->user->id,
    ]);
});

it('may cancel a completed transfer and reverse stock', function (): void {
    $transfer = StockTransfer::factory()->completed()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('inventory.stock-transfers.cancel', $transfer));

    $response->assertRedirect();

    $this->assertDatabaseHas('stock_transfers', [
        'id' => $transfer->id,
        'status' => StockTransferStatusEnum::CANCELLED->value,
    ]);
});

it('returns already cancelled transfer without error', function (): void {
    $transfer = StockTransfer::factory()->cancelled()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('inventory.stock-transfers.cancel', $transfer));

    $response->assertRedirect();
    $response->assertSessionDoesntHaveErrors();
});
