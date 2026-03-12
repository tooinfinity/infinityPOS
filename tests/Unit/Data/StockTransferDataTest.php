<?php

declare(strict_types=1);

use App\Data\StockTransfer\StockTransferData;
use App\Data\StockTransfer\StockTransferItemData;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\DataCollection;

describe(StockTransferData::class, function (): void {
    describe('creation', function (): void {
        it('creates with required fields', function (): void {
            $items = new DataCollection(StockTransferItemData::class, []);

            $data = new StockTransferData(
                from_warehouse_id: 1,
                to_warehouse_id: 2,
                transfer_date: Illuminate\Support\Facades\Date::parse('2024-01-15'),
                note: null,
                items: $items,
            );

            expect($data)->toBeInstanceOf(StockTransferData::class)
                ->and($data->from_warehouse_id)->toBe(1)
                ->and($data->to_warehouse_id)->toBe(2)
                ->and($data->transfer_date)->toBeInstanceOf(CarbonInterface::class);
        });

        it('creates with all fields', function (): void {
            $items = new DataCollection(StockTransferItemData::class, [
                new StockTransferItemData(
                    product_id: 1,
                    batch_id: null,
                    quantity: 10,
                ),
            ]);

            $data = new StockTransferData(
                from_warehouse_id: 1,
                to_warehouse_id: 2,
                transfer_date: Illuminate\Support\Facades\Date::parse('2024-01-15'),
                note: 'Test transfer',
                items: $items,
            );

            expect($data->note)->toBe('Test transfer')
                ->and($data->items)->toHaveCount(1);
        });
    });

    describe('fromModel', function (): void {
        it('creates data from model', function (): void {
            $fromWarehouse = Warehouse::factory()->create();
            $toWarehouse = Warehouse::factory()->create();

            $transfer = StockTransfer::factory()->create([
                'from_warehouse_id' => $fromWarehouse->id,
                'to_warehouse_id' => $toWarehouse->id,
                'transfer_date' => '2024-01-20',
            ]);

            $data = StockTransferData::fromModel($transfer);

            expect($data)->toBeInstanceOf(StockTransferData::class)
                ->and($data->from_warehouse_id)->toBe($fromWarehouse->id)
                ->and($data->to_warehouse_id)->toBe($toWarehouse->id);
        });
    });

    describe('validation', function (): void {
        it('passes validation with valid data', function (): void {
            $fromWarehouse = Warehouse::factory()->create();
            $toWarehouse = Warehouse::factory()->create();
            $product = Product::factory()->create();

            $validated = StockTransferData::validate([
                'from_warehouse_id' => $fromWarehouse->id,
                'to_warehouse_id' => $toWarehouse->id,
                'transfer_date' => '2024-01-15',
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 10],
                ],
            ]);

            expect($validated['from_warehouse_id'])->toBe($fromWarehouse->id);
        });

        it('fails validation when from_warehouse_id is missing', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => StockTransferData::validate([
                'to_warehouse_id' => 2,
                'transfer_date' => '2024-01-15',
                'items' => [],
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when to_warehouse_id is missing', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => StockTransferData::validate([
                'from_warehouse_id' => 1,
                'transfer_date' => '2024-01-15',
                'items' => [],
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when warehouses are the same', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => StockTransferData::validate([
                'from_warehouse_id' => 1,
                'to_warehouse_id' => 1,
                'transfer_date' => '2024-01-15',
                'items' => [],
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when items is empty', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => StockTransferData::validate([
                'from_warehouse_id' => 1,
                'to_warehouse_id' => 2,
                'transfer_date' => '2024-01-15',
                'items' => [],
            ]))->toThrow(ValidationException::class);
        });
    });
});
