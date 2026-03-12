<?php

declare(strict_types=1);

use App\Data\Pos\PosCartItemData;
use App\Models\Product;
use Illuminate\Validation\ValidationException;

describe(PosCartItemData::class, function (): void {
    describe('creation', function (): void {
        it('creates with required fields', function (): void {
            $data = new PosCartItemData(
                product_id: 1,
                batch_id: null,
                quantity: 5,
                unit_price: 1000,
                unit_cost: 500,
            );

            expect($data)->toBeInstanceOf(PosCartItemData::class)
                ->and($data->product_id)->toBe(1)
                ->and($data->batch_id)->toBeNull()
                ->and($data->quantity)->toBe(5)
                ->and($data->unit_price)->toBe(1000)
                ->and($data->unit_cost)->toBe(500);
        });

        it('creates with batch_id', function (): void {
            $data = new PosCartItemData(
                product_id: 1,
                batch_id: 10,
                quantity: 3,
                unit_price: 1500,
                unit_cost: 750,
            );

            expect($data->batch_id)->toBe(10);
        });
    });

    describe('validation', function (): void {
        it('passes validation with valid data', function (): void {
            $product = Product::factory()->create();

            $validated = PosCartItemData::validate([
                'product_id' => $product->id,
                'quantity' => 5,
                'unit_price' => 1000,
                'unit_cost' => 500,
            ]);

            expect($validated['quantity'])->toBe(5);
        });

        it('fails validation when product_id is missing', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => PosCartItemData::validate([
                'quantity' => 5,
                'unit_price' => 1000,
                'unit_cost' => 500,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation with quantity less than 1', function (): void {
            $product = Product::factory()->create();

            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => PosCartItemData::validate([
                'product_id' => $product->id,
                'quantity' => 0,
                'unit_price' => 1000,
                'unit_cost' => 500,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation with negative unit_price', function (): void {
            $product = Product::factory()->create();

            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => PosCartItemData::validate([
                'product_id' => $product->id,
                'quantity' => 5,
                'unit_price' => -1,
                'unit_cost' => 500,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation with negative unit_cost', function (): void {
            $product = Product::factory()->create();

            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => PosCartItemData::validate([
                'product_id' => $product->id,
                'quantity' => 5,
                'unit_price' => 1000,
                'unit_cost' => -1,
            ]))->toThrow(ValidationException::class);
        });
    });
});
