<?php

declare(strict_types=1);

use App\Data\PurchaseReturn\PurchaseReturnData;
use App\Data\PurchaseReturn\PurchaseReturnItemData;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Warehouse;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\DataCollection;

describe(PurchaseReturnData::class, function (): void {
    describe('creation', function (): void {
        it('creates with required fields', function (): void {
            $items = new DataCollection(PurchaseReturnItemData::class, []);

            $data = new PurchaseReturnData(
                purchase_id: 1,
                warehouse_id: 1,
                return_date: '2024-01-15',
                note: null,
                items: $items,
            );

            expect($data)->toBeInstanceOf(PurchaseReturnData::class)
                ->and($data->purchase_id)->toBe(1)
                ->and($data->warehouse_id)->toBe(1)
                ->and($data->return_date)->toBe('2024-01-15');
        });

        it('creates with all fields', function (): void {
            $items = new DataCollection(PurchaseReturnItemData::class, [
                new PurchaseReturnItemData(
                    product_id: 1,
                    batch_id: null,
                    quantity: 2,
                    unit_cost: 500,
                ),
            ]);

            $data = new PurchaseReturnData(
                purchase_id: 1,
                warehouse_id: 1,
                return_date: '2024-01-15',
                note: 'Test return',
                items: $items,
            );

            expect($data->note)->toBe('Test return')
                ->and($data->items)->toHaveCount(1);
        });
    });

    describe('validation', function (): void {
        it('passes validation with valid data', function (): void {
            $purchase = Purchase::factory()->create();
            $warehouse = Warehouse::factory()->create();
            $product = Product::factory()->create();

            $validated = PurchaseReturnData::validate([
                'purchase_id' => $purchase->id,
                'warehouse_id' => $warehouse->id,
                'return_date' => '2024-01-15',
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 2, 'unit_cost' => 500],
                ],
            ]);

            expect($validated['purchase_id'])->toBe($purchase->id);
        });

        it('fails validation when purchase_id is missing', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => PurchaseReturnData::validate([
                'warehouse_id' => 1,
                'return_date' => '2024-01-15',
                'items' => [],
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when warehouse_id is missing', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => PurchaseReturnData::validate([
                'purchase_id' => 1,
                'return_date' => '2024-01-15',
                'items' => [],
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when items is empty', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => PurchaseReturnData::validate([
                'purchase_id' => 1,
                'warehouse_id' => 1,
                'return_date' => '2024-01-15',
                'items' => [],
            ]))->toThrow(ValidationException::class);
        });
    });
});
