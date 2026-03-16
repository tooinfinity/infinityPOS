<?php

declare(strict_types=1);

use App\Data\Sale\SaleData;
use App\Data\Sale\SaleItemData;
use App\Enums\SaleStatusEnum;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Warehouse;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\DataCollection;

describe(SaleData::class, function (): void {
    describe('creation', function (): void {
        it('creates with required fields', function (): void {
            $items = new DataCollection(SaleItemData::class, []);

            $data = new SaleData(
                customer_id: null,
                warehouse_id: 1,
                status: SaleStatusEnum::Pending,
                sale_date: Illuminate\Support\Facades\Date::parse('2024-01-15'),
                total_amount: 5000,
                note: null,
                items: $items,
                paid_amount: 0,
                change_amount: 0,
            );

            expect($data)->toBeInstanceOf(SaleData::class)
                ->and($data->customer_id)->toBeNull()
                ->and($data->warehouse_id)->toBe(1)
                ->and($data->status)->toBe(SaleStatusEnum::Pending)
                ->and($data->total_amount)->toBe(5000);
        });

        it('creates with all fields', function (): void {
            $items = new DataCollection(SaleItemData::class, [
                new SaleItemData(
                    product_id: 1,
                    batch_id: null,
                    quantity: 5,
                    unit_price: 1000,
                    unit_cost: 500,
                ),
            ]);

            $data = new SaleData(
                customer_id: 1,
                warehouse_id: 1,
                status: SaleStatusEnum::Completed,
                sale_date: Illuminate\Support\Facades\Date::parse('2024-01-15'),
                total_amount: 5000,
                note: 'Test sale',
                items: $items,
                paid_amount: 5000,
                change_amount: 0,
            );

            expect($data->customer_id)->toBe(1)
                ->and($data->note)->toBe('Test sale')
                ->and($data->items)->toHaveCount(1);
        });
    });

    describe('fromModel', function (): void {
        it('creates data from model', function (): void {
            $sale = Sale::factory()->create();

            $data = SaleData::fromModel($sale);

            expect($data)->toBeInstanceOf(SaleData::class)
                ->and($data->customer_id)->toBe($sale->customer_id)
                ->and($data->total_amount)->toBe($sale->total_amount);
        });
    });

    describe('validation', function (): void {
        it('passes validation with valid data', function (): void {
            $warehouse = Warehouse::factory()->create();
            $product = Product::factory()->create();

            $validated = SaleData::validate([
                'warehouse_id' => $warehouse->id,
                'status' => 'pending',
                'sale_date' => '2024-01-15',
                'total_amount' => 1000,
                'paid_amount' => 500,
                'change_amount' => 0,
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 500, 'unit_cost' => 250],
                ],
            ]);

            expect($validated['total_amount'])->toBe(1000);
        });

        it('fails validation when warehouse_id is missing', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => SaleData::validate([
                'status' => 'pending',
                'sale_date' => '2024-01-15',
                'total_amount' => 1000,
                'paid_amount' => 500,
                'change_amount' => 0,
                'items' => [],
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation with negative total_amount', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => SaleData::validate([
                'warehouse_id' => 1,
                'status' => 'pending',
                'sale_date' => '2024-01-15',
                'total_amount' => -1,
                'paid_amount' => 0,
                'change_amount' => 0,
                'items' => [],
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when items is empty', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => SaleData::validate([
                'warehouse_id' => 1,
                'status' => 'pending',
                'sale_date' => '2024-01-15',
                'total_amount' => 1000,
                'paid_amount' => 500,
                'change_amount' => 0,
                'items' => [],
            ]))->toThrow(ValidationException::class);
        });
    });
});
