<?php

declare(strict_types=1);

use App\Data\Purchase\ReceivePurchaseItemData;
use App\Models\PurchaseItem;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;

describe(ReceivePurchaseItemData::class, function (): void {
    describe('creation', function (): void {
        it('creates with required fields', function (): void {
            $data = new ReceivePurchaseItemData(
                purchase_item_id: 1,
                received_quantity: 10,
                expires_at: null,
            );

            expect($data)->toBeInstanceOf(ReceivePurchaseItemData::class)
                ->and($data->purchase_item_id)->toBe(1)
                ->and($data->received_quantity)->toBe(10)
                ->and($data->expires_at)->toBeNull();
        });

        it('creates with expires_at', function (): void {
            $data = new ReceivePurchaseItemData(
                purchase_item_id: 1,
                received_quantity: 5,
                expires_at: Illuminate\Support\Facades\Date::parse('2025-12-31'),
            );

            expect($data->expires_at)->toBeInstanceOf(CarbonInterface::class);
        });
    });

    describe('validation', function (): void {
        it('passes validation with valid data', function (): void {
            $purchaseItem = PurchaseItem::factory()->create();

            $validated = ReceivePurchaseItemData::validate([
                'purchase_item_id' => $purchaseItem->id,
                'received_quantity' => 10,
            ]);

            expect($validated['received_quantity'])->toBe(10);
        });

        it('fails validation when purchase_item_id is missing', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => ReceivePurchaseItemData::validate([
                'received_quantity' => 10,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation with negative received_quantity', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => ReceivePurchaseItemData::validate([
                'purchase_item_id' => 1,
                'received_quantity' => -1,
            ]))->toThrow(ValidationException::class);
        });
    });
});
