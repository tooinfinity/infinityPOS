<?php

declare(strict_types=1);

use App\Data\Purchase\PurchaseData;
use App\Data\Purchase\PurchaseItemData;
use App\Enums\PurchaseStatusEnum;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\DataCollection;

describe(PurchaseData::class, function (): void {
    describe('creation', function (): void {
        it('creates with required fields', function (): void {
            $items = new DataCollection(PurchaseItemData::class, []);

            $data = new PurchaseData(
                supplier_id: 1,
                warehouse_id: 1,
                status: PurchaseStatusEnum::Pending,
                purchase_date: Illuminate\Support\Facades\Date::parse('2024-01-15'),
                total_amount: 5000,
                note: null,
                items: $items,
            );

            expect($data)->toBeInstanceOf(PurchaseData::class)
                ->and($data->supplier_id)->toBe(1)
                ->and($data->warehouse_id)->toBe(1)
                ->and($data->status)->toBe(PurchaseStatusEnum::Pending)
                ->and($data->total_amount)->toBe(5000);
        });

        it('creates with all fields', function (): void {
            $items = new DataCollection(PurchaseItemData::class, [
                new PurchaseItemData(
                    product_id: 1,
                    quantity: 10,
                    unit_cost: 500,
                    expires_at: null,
                ),
            ]);

            $data = new PurchaseData(
                supplier_id: 1,
                warehouse_id: 1,
                status: PurchaseStatusEnum::Received,
                purchase_date: Illuminate\Support\Facades\Date::parse('2024-01-15'),
                total_amount: 5000,
                note: 'Test purchase',
                items: $items,
            );

            expect($data->note)->toBe('Test purchase')
                ->and($data->items)->toHaveCount(1);
        });
    });

    describe('fromModel', function (): void {
        it('creates data from model', function (): void {
            $purchase = Purchase::factory()->create();

            $data = PurchaseData::fromModel($purchase);

            expect($data)->toBeInstanceOf(PurchaseData::class)
                ->and($data->supplier_id)->toBe($purchase->supplier_id)
                ->and($data->total_amount)->toBe($purchase->total_amount);
        });
    });

    describe('validation', function (): void {
        it('passes validation with valid data', function (): void {
            $supplier = Supplier::factory()->create();
            $warehouse = Warehouse::factory()->create();
            $product = Product::factory()->create();

            $validated = PurchaseData::validate([
                'supplier_id' => $supplier->id,
                'warehouse_id' => $warehouse->id,
                'status' => 'pending',
                'purchase_date' => '2024-01-15',
                'total_amount' => 1000,
                'paid_amount' => 500,
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 5, 'unit_cost' => 100],
                ],
            ]);

            expect($validated['total_amount'])->toBe(1000);
        });

        it('fails validation when supplier_id is missing', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => PurchaseData::validate([
                'warehouse_id' => 1,
                'status' => 'pending',
                'purchase_date' => '2024-01-15',
                'total_amount' => 1000,
                'paid_amount' => 500,
                'items' => [],
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when warehouse_id is missing', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => PurchaseData::validate([
                'supplier_id' => 1,
                'status' => 'pending',
                'purchase_date' => '2024-01-15',
                'total_amount' => 1000,
                'paid_amount' => 500,
                'items' => [],
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation with negative total_amount', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => PurchaseData::validate([
                'supplier_id' => 1,
                'warehouse_id' => 1,
                'status' => 'pending',
                'purchase_date' => '2024-01-15',
                'total_amount' => -1,
                'paid_amount' => 0,
                'items' => [],
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when items is empty', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => PurchaseData::validate([
                'supplier_id' => 1,
                'warehouse_id' => 1,
                'status' => 'pending',
                'purchase_date' => '2024-01-15',
                'total_amount' => 1000,
                'paid_amount' => 500,
                'items' => [],
            ]))->toThrow(ValidationException::class);
        });
    });
});
