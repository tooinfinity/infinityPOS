<?php

declare(strict_types=1);

use App\Data\Purchase\PurchaseItemData;
use App\Models\Product;
use Illuminate\Validation\ValidationException;

describe(PurchaseItemData::class, function (): void {
    describe('creation', function (): void {
        it('creates with required fields', function (): void {
            $data = new PurchaseItemData(
                product_id: 1,
                quantity: 10,
                unit_cost: 500,
                expires_at: null,
            );

            expect($data)->toBeInstanceOf(PurchaseItemData::class)
                ->and($data->product_id)->toBe(1)
                ->and($data->quantity)->toBe(10)
                ->and($data->unit_cost)->toBe(500)
                ->and($data->expires_at)->toBeNull();
        });

        it('creates with expires_at', function (): void {
            $data = new PurchaseItemData(
                product_id: 1,
                quantity: 5,
                unit_cost: 300,
                expires_at: '2025-12-31',
            );

            expect($data->expires_at)->toBe('2025-12-31');
        });
    });

    describe('validation', function (): void {
        it('passes validation with valid data', function (): void {
            $product = Product::factory()->create();

            $validated = PurchaseItemData::validate([
                'product_id' => $product->id,
                'quantity' => 10,
                'unit_cost' => 500,
            ]);

            expect($validated['quantity'])->toBe(10);
        });

        it('fails validation when product_id is missing', function (): void {
            expect(fn (): Illuminate\Contracts\Support\Arrayable|array => PurchaseItemData::validate([
                'quantity' => 10,
                'unit_cost' => 500,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation with quantity less than 1', function (): void {
            expect(fn (): Illuminate\Contracts\Support\Arrayable|array => PurchaseItemData::validate([
                'product_id' => 1,
                'quantity' => 0,
                'unit_cost' => 500,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation with negative unit_cost', function (): void {
            expect(fn (): Illuminate\Contracts\Support\Arrayable|array => PurchaseItemData::validate([
                'product_id' => 1,
                'quantity' => 10,
                'unit_cost' => -1,
            ]))->toThrow(ValidationException::class);
        });
    });
});
