<?php

declare(strict_types=1);

use App\Data\Product\ProductData;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Validation\ValidationException;

describe(ProductData::class, function (): void {
    describe('creation', function (): void {
        it('creates with required fields', function (): void {
            $data = new ProductData(
                name: 'Test Product',
                sku: null,
                barcode: null,
                unit_id: 1,
                category_id: null,
                brand_id: null,
                description: null,
                cost_price: 1000,
                selling_price: 1500,
                alert_quantity: 10,
                track_inventory: true,
                is_active: true,
            );

            expect($data)->toBeInstanceOf(ProductData::class)
                ->and($data->name)->toBe('Test Product')
                ->and($data->unit_id)->toBe(1)
                ->and($data->cost_price)->toBe(1000)
                ->and($data->selling_price)->toBe(1500)
                ->and($data->track_inventory)->toBeTrue()
                ->and($data->is_active)->toBeTrue();
        });

        it('creates with all optional fields', function (): void {
            $data = new ProductData(
                name: 'Test Product',
                sku: 'SKU001',
                barcode: '123456789',
                unit_id: 1,
                category_id: 1,
                brand_id: 1,
                description: 'A test product',
                cost_price: 1000,
                selling_price: 1500,
                alert_quantity: 10,
                track_inventory: true,
                is_active: true,
            );

            expect($data->sku)->toBe('SKU001')
                ->and($data->barcode)->toBe('123456789')
                ->and($data->category_id)->toBe(1)
                ->and($data->brand_id)->toBe(1)
                ->and($data->description)->toBe('A test product');
        });
    });

    describe('fromModel', function (): void {
        it('creates data from model', function (): void {
            $unit = Unit::factory()->create();
            $category = Category::factory()->create();
            $brand = Brand::factory()->create();
            $product = Product::factory()->create([
                'unit_id' => $unit->id,
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'name' => 'Model Product',
                'cost_price' => 500,
                'selling_price' => 750,
                'track_inventory' => true,
            ]);

            $data = ProductData::fromModel($product);

            expect($data)->toBeInstanceOf(ProductData::class)
                ->and($data->name)->toBe('Model Product')
                ->and($data->cost_price)->toBe(500)
                ->and($data->selling_price)->toBe(750);
        });
    });

    describe('validation', function (): void {
        it('passes validation with valid data', function (): void {
            $unit = Unit::factory()->create();

            $validated = ProductData::validate([
                'name' => 'Valid Product',
                'sku' => 'SKU001',
                'barcode' => '123456',
                'unit_id' => $unit->id,
                'cost_price' => 1000,
                'selling_price' => 1500,
                'alert_quantity' => 10,
                'track_inventory' => true,
                'is_active' => true,
            ]);

            expect($validated['name'])->toBe('Valid Product');
        });

        it('fails validation when name is too short', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => ProductData::validate([
                'name' => 'AB',
                'unit_id' => 1,
                'cost_price' => 1000,
                'selling_price' => 1500,
                'alert_quantity' => 10,
                'track_inventory' => true,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when unit_id is missing', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => ProductData::validate([
                'name' => 'Valid Product',
                'cost_price' => 1000,
                'selling_price' => 1500,
                'alert_quantity' => 10,
                'track_inventory' => true,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation with negative cost_price', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => ProductData::validate([
                'name' => 'Valid Product',
                'unit_id' => 1,
                'cost_price' => -1,
                'selling_price' => 1500,
                'alert_quantity' => 10,
                'track_inventory' => true,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation with negative selling_price', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => ProductData::validate([
                'name' => 'Valid Product',
                'unit_id' => 1,
                'cost_price' => 1000,
                'selling_price' => -1,
                'alert_quantity' => 10,
                'track_inventory' => true,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation with negative alert_quantity', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => ProductData::validate([
                'name' => 'Valid Product',
                'unit_id' => 1,
                'cost_price' => 1000,
                'selling_price' => 1500,
                'alert_quantity' => -1,
                'track_inventory' => true,
            ]))->toThrow(ValidationException::class);
        });
    });
});
