<?php

declare(strict_types=1);

use App\Data\PurchaseReturn\PurchaseReturnItemData;
use App\Models\Product;
use Illuminate\Validation\ValidationException;

describe(PurchaseReturnItemData::class, function (): void {
    describe('creation', function (): void {
        it('creates with required fields', function (): void {
            $data = new PurchaseReturnItemData(
                product_id: 1,
                batch_id: null,
                quantity: 5,
                unit_cost: 500,
            );

            expect($data)->toBeInstanceOf(PurchaseReturnItemData::class)
                ->and($data->product_id)->toBe(1)
                ->and($data->batch_id)->toBeNull()
                ->and($data->quantity)->toBe(5)
                ->and($data->unit_cost)->toBe(500);
        });

        it('creates with batch_id', function (): void {
            $data = new PurchaseReturnItemData(
                product_id: 1,
                batch_id: 10,
                quantity: 3,
                unit_cost: 300,
            );

            expect($data->batch_id)->toBe(10);
        });
    });

    describe('validation', function (): void {
        it('passes validation with valid data', function (): void {
            $product = Product::factory()->create();

            $validated = PurchaseReturnItemData::validate([
                'product_id' => $product->id,
                'quantity' => 5,
                'unit_cost' => 500,
            ]);

            expect($validated['quantity'])->toBe(5);
        });

        it('fails validation when product_id is missing', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => PurchaseReturnItemData::validate([
                'quantity' => 5,
                'unit_cost' => 500,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation with quantity less than 1', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => PurchaseReturnItemData::validate([
                'product_id' => 1,
                'quantity' => 0,
                'unit_cost' => 500,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation with negative unit_cost', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => PurchaseReturnItemData::validate([
                'product_id' => 1,
                'quantity' => 5,
                'unit_cost' => -1,
            ]))->toThrow(ValidationException::class);
        });
    });
});
