<?php

declare(strict_types=1);

use App\Data\SaleReturn\SaleReturnData;
use App\Data\SaleReturn\SaleReturnItemData;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Warehouse;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\DataCollection;

describe(SaleReturnData::class, function (): void {
    describe('creation', function (): void {
        it('creates with required fields', function (): void {
            $items = new DataCollection(SaleReturnItemData::class, []);

            $data = new SaleReturnData(
                sale_id: 1,
                warehouse_id: 1,
                return_date: Illuminate\Support\Facades\Date::parse('2024-01-15'),
                note: null,
                items: $items,
            );

            expect($data)->toBeInstanceOf(SaleReturnData::class)
                ->and($data->sale_id)->toBe(1)
                ->and($data->warehouse_id)->toBe(1)
                ->and($data->return_date)->toBeInstanceOf(CarbonInterface::class);
        });

        it('creates with all fields', function (): void {
            $items = new DataCollection(SaleReturnItemData::class, [
                new SaleReturnItemData(
                    product_id: 1,
                    batch_id: null,
                    quantity: 2,
                    unit_price: 500,
                ),
            ]);

            $data = new SaleReturnData(
                sale_id: 1,
                warehouse_id: 1,
                return_date: Illuminate\Support\Facades\Date::parse('2024-01-15'),
                note: 'Test return',
                items: $items,
            );

            expect($data->note)->toBe('Test return')
                ->and($data->items)->toHaveCount(1);
        });
    });

    describe('validation', function (): void {
        it('passes validation with valid data', function (): void {
            $sale = Sale::factory()->create();
            $warehouse = Warehouse::factory()->create();
            $product = Product::factory()->create();

            $validated = SaleReturnData::validate([
                'sale_id' => $sale->id,
                'warehouse_id' => $warehouse->id,
                'return_date' => '2024-01-15',
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 500],
                ],
            ]);

            expect($validated['sale_id'])->toBe($sale->id);
        });

        it('fails validation when sale_id is missing', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => SaleReturnData::validate([
                'warehouse_id' => 1,
                'return_date' => '2024-01-15',
                'items' => [],
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when warehouse_id is missing', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => SaleReturnData::validate([
                'sale_id' => 1,
                'return_date' => '2024-01-15',
                'items' => [],
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when items is empty', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => SaleReturnData::validate([
                'sale_id' => 1,
                'warehouse_id' => 1,
                'return_date' => '2024-01-15',
                'items' => [],
            ]))->toThrow(ValidationException::class);
        });
    });
});
