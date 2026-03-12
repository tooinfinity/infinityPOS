<?php

declare(strict_types=1);

use App\Data\Purchase\ReceivePurchaseData;
use App\Data\Purchase\ReceivePurchaseItemData;
use App\Models\PurchaseItem;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\DataCollection;

describe(ReceivePurchaseData::class, function (): void {
    describe('creation', function (): void {
        it('creates with required fields', function (): void {
            $items = new DataCollection(ReceivePurchaseItemData::class, []);

            $data = new ReceivePurchaseData(
                items: $items,
            );

            expect($data)->toBeInstanceOf(ReceivePurchaseData::class)
                ->and($data->items)->toBeInstanceOf(DataCollection::class);
        });

        it('creates with items', function (): void {
            $items = new DataCollection(ReceivePurchaseItemData::class, [
                new ReceivePurchaseItemData(
                    purchase_item_id: 1,
                    received_quantity: 10,
                    expires_at: null,
                ),
            ]);

            $data = new ReceivePurchaseData(
                items: $items,
            );

            expect($data->items)->toHaveCount(1);
        });
    });

    describe('validation', function (): void {
        it('passes validation with valid data', function (): void {
            $purchaseItem = PurchaseItem::factory()->create();

            $validated = ReceivePurchaseData::validate([
                'items' => [
                    ['purchase_item_id' => $purchaseItem->id, 'received_quantity' => 10],
                ],
            ]);

            expect($validated['items'])->toHaveCount(1);
        });

        it('fails validation when items is empty', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => ReceivePurchaseData::validate([
                'items' => [],
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when purchase_item_id is missing', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => ReceivePurchaseData::validate([
                'items' => [
                    ['received_quantity' => 10],
                ],
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation with negative received_quantity', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => ReceivePurchaseData::validate([
                'items' => [
                    ['purchase_item_id' => 1, 'received_quantity' => -1],
                ],
            ]))->toThrow(ValidationException::class);
        });
    });
});
