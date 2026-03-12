<?php

declare(strict_types=1);

use App\Data\Pos\PosCartItemData;
use App\Data\Pos\PosOrderData;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\DataCollection;

describe(PosOrderData::class, function (): void {
    describe('creation', function (): void {
        it('creates with required fields', function (): void {
            $data = new PosOrderData(
                customer_id: null,
                warehouse_id: 1,
                payment_method_id: 1,
                cash_tendered: 5000,
                total_amount: 4500,
                note: null,
                items: new DataCollection(PosCartItemData::class, []),
            );

            expect($data)->toBeInstanceOf(PosOrderData::class)
                ->and($data->customer_id)->toBeNull()
                ->and($data->warehouse_id)->toBe(1)
                ->and($data->payment_method_id)->toBe(1)
                ->and($data->cash_tendered)->toBe(5000)
                ->and($data->total_amount)->toBe(4500);
        });

        it('creates with all optional fields', function (): void {
            $items = new DataCollection(PosCartItemData::class, [
                new PosCartItemData(
                    product_id: 1,
                    batch_id: null,
                    quantity: 2,
                    unit_price: 1000,
                    unit_cost: 500,
                ),
            ]);

            $data = new PosOrderData(
                customer_id: 1,
                warehouse_id: 1,
                payment_method_id: 1,
                cash_tendered: 5000,
                total_amount: 2000,
                note: 'Test order',
                items: $items,
            );

            expect($data->customer_id)->toBe(1)
                ->and($data->note)->toBe('Test order')
                ->and($data->items)->toHaveCount(1);
        });
    });

    describe('validation', function (): void {
        it('passes validation with valid data', function (): void {
            $warehouse = Warehouse::factory()->create();
            $method = PaymentMethod::factory()->create();
            $product = Product::factory()->create();

            $validated = PosOrderData::validate([
                'warehouse_id' => $warehouse->id,
                'payment_method_id' => $method->id,
                'cash_tendered' => 5000,
                'total_amount' => 4500,
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 1000, 'unit_cost' => 500],
                ],
            ]);

            expect($validated['warehouse_id'])->toBe($warehouse->id);
        });

        it('fails validation when warehouse_id is missing', function (): void {
            $method = PaymentMethod::factory()->create();

            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => PosOrderData::validate([
                'payment_method_id' => $method->id,
                'cash_tendered' => 5000,
                'total_amount' => 4500,
                'items' => [],
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when payment_method_id is missing', function (): void {
            $warehouse = Warehouse::factory()->create();

            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => PosOrderData::validate([
                'warehouse_id' => $warehouse->id,
                'cash_tendered' => 5000,
                'total_amount' => 4500,
                'items' => [],
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation with negative cash_tendered', function (): void {
            $warehouse = Warehouse::factory()->create();
            $method = PaymentMethod::factory()->create();

            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => PosOrderData::validate([
                'warehouse_id' => $warehouse->id,
                'payment_method_id' => $method->id,
                'cash_tendered' => -1,
                'total_amount' => 4500,
                'items' => [],
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation with total_amount less than 1', function (): void {
            $warehouse = Warehouse::factory()->create();
            $method = PaymentMethod::factory()->create();

            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => PosOrderData::validate([
                'warehouse_id' => $warehouse->id,
                'payment_method_id' => $method->id,
                'cash_tendered' => 5000,
                'total_amount' => 0,
                'items' => [],
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when items array is empty', function (): void {
            $warehouse = Warehouse::factory()->create();
            $method = PaymentMethod::factory()->create();

            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => PosOrderData::validate([
                'warehouse_id' => $warehouse->id,
                'payment_method_id' => $method->id,
                'cash_tendered' => 5000,
                'total_amount' => 4500,
                'items' => [],
            ]))->toThrow(ValidationException::class);
        });
    });
});
