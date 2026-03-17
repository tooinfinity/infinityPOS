<?php

declare(strict_types=1);

use App\Data\Batch\BatchData;
use App\Models\Batch;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Validation\ValidationException;

describe(BatchData::class, function (): void {
    describe('creation', function (): void {
        it('creates with required fields', function (): void {
            $data = new BatchData(
                product_id: 1,
                warehouse_id: 1,
                batch_number: 'BATCH001',
                cost_amount: 1000,
                quantity: 50,
                expires_at: null,
            );

            expect($data)->toBeInstanceOf(BatchData::class)
                ->and($data->product_id)->toBe(1)
                ->and($data->warehouse_id)->toBe(1)
                ->and($data->batch_number)->toBe('BATCH001')
                ->and($data->cost_amount)->toBe(1000)
                ->and($data->quantity)->toBe(50)
                ->and($data->expires_at)->toBeNull()
                ->and($data->user_id)->toBeNull();
        });

        it('creates with all fields including expires_at', function (): void {
            $data = new BatchData(
                product_id: 1,
                warehouse_id: 1,
                batch_number: 'BATCH001',
                cost_amount: 1000,
                quantity: 50,
                expires_at: '2025-12-31',
                user_id: 1,
            );

            expect($data->expires_at)->toBe('2025-12-31')
                ->and($data->user_id)->toBe(1);
        });
    });

    describe('fromModel', function (): void {
        it('creates data from model', function (): void {
            $product = Product::factory()->create();
            $warehouse = Warehouse::factory()->create();
            $batch = Batch::factory()->create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'batch_number' => 'TEST001',
                'cost_amount' => 500,
                'quantity' => 25,
            ]);

            $data = BatchData::fromModel($batch);

            expect($data)->toBeInstanceOf(BatchData::class)
                ->and($data->product_id)->toBe($product->id)
                ->and($data->batch_number)->toBe('TEST001')
                ->and($data->cost_amount)->toBe(500);
        });
    });

    describe('validation', function (): void {
        beforeEach(function (): void {
            $this->product = Product::factory()->create();
            $this->warehouse = Warehouse::factory()->create();
        });

        it('passes validation with valid data', function (): void {
            $validated = BatchData::validate([
                'product_id' => $this->product->id,
                'warehouse_id' => $this->warehouse->id,
                'batch_number' => 'BATCH001',
                'cost_amount' => 1000,
                'quantity' => 50,
            ]);

            expect($validated['batch_number'])->toBe('BATCH001');
        });

        it('fails validation when product_id is missing', function (): void {
            expect(fn (): Illuminate\Contracts\Support\Arrayable|array => BatchData::validate([
                'warehouse_id' => $this->warehouse->id,
                'batch_number' => 'BATCH001',
                'cost_amount' => 1000,
                'quantity' => 50,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when warehouse_id is missing', function (): void {
            expect(fn (): Illuminate\Contracts\Support\Arrayable|array => BatchData::validate([
                'product_id' => $this->product->id,
                'batch_number' => 'BATCH001',
                'cost_amount' => 1000,
                'quantity' => 50,
            ]))->toThrow(ValidationException::class);
        });

        it('passes validation when batch_number is missing (nullable)', function (): void {
            $validated = BatchData::validate([
                'product_id' => $this->product->id,
                'warehouse_id' => $this->warehouse->id,
                'cost_amount' => 1000,
                'quantity' => 50,
            ]);

            expect($validated['product_id'])->toBe($this->product->id);
        });

        it('fails validation with negative cost_amount', function (): void {
            expect(fn (): Illuminate\Contracts\Support\Arrayable|array => BatchData::validate([
                'product_id' => $this->product->id,
                'warehouse_id' => $this->warehouse->id,
                'batch_number' => 'BATCH001',
                'cost_amount' => -1,
                'quantity' => 50,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation with negative quantity', function (): void {
            expect(fn (): Illuminate\Contracts\Support\Arrayable|array => BatchData::validate([
                'product_id' => $this->product->id,
                'warehouse_id' => $this->warehouse->id,
                'batch_number' => 'BATCH001',
                'cost_amount' => 1000,
                'quantity' => -1,
            ]))->toThrow(ValidationException::class);
        });
    });
});
