<?php

declare(strict_types=1);

use App\Data\StockTransfer\StockTransferItemData;
use App\Models\Product;
use Illuminate\Validation\ValidationException;

describe(StockTransferItemData::class, function (): void {
    describe('creation', function (): void {
        it('creates with required fields', function (): void {
            $data = new StockTransferItemData(
                product_id: 1,
                batch_id: null,
                quantity: 10,
            );

            expect($data)->toBeInstanceOf(StockTransferItemData::class)
                ->and($data->product_id)->toBe(1)
                ->and($data->batch_id)->toBeNull()
                ->and($data->quantity)->toBe(10);
        });

        it('creates with batch_id', function (): void {
            $data = new StockTransferItemData(
                product_id: 1,
                batch_id: 5,
                quantity: 8,
            );

            expect($data->batch_id)->toBe(5);
        });
    });

    describe('validation', function (): void {
        it('passes validation with valid data', function (): void {
            $product = Product::factory()->create();

            $validated = StockTransferItemData::validate([
                'product_id' => $product->id,
                'quantity' => 10,
            ]);

            expect($validated['quantity'])->toBe(10);
        });

        it('fails validation when product_id is missing', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => StockTransferItemData::validate([
                'quantity' => 10,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation with quantity less than 1', function (): void {
            $product = Product::factory()->create();

            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => StockTransferItemData::validate([
                'product_id' => $product->id,
                'quantity' => 0,
            ]))->toThrow(ValidationException::class);
        });
    });
});
