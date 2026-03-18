<?php

declare(strict_types=1);

use App\Models\Batch;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator;

describe('ProductBuilder', function (): void {
    describe('forSaleForm', function (): void {
        it('exists as a method', function (): void {
            $builder = Product::query();

            expect(method_exists($builder, 'forSaleForm'))->toBeTrue();
        });

        it('returns EloquentCollection', function (): void {
            Product::factory()->count(3)->create();

            $result = Product::query()->forSaleForm();

            expect($result)->toBeInstanceOf(EloquentCollection::class);
        });

        it('returns collection of Product models', function (): void {
            Product::factory()->count(3)->create();

            $result = Product::query()->forSaleForm();

            expect($result->first())->toBeInstanceOf(Product::class);
        });

        it('eager loads unit relationship', function (): void {
            Product::factory()->create();

            $result = Product::query()->forSaleForm()->first();

            expect($result->relationLoaded('unit'))->toBeTrue();
        });

        it('includes stock_quantity attribute', function (): void {
            $product = Product::factory()->create();
            Batch::factory()->create([
                'product_id' => $product->id,
                'quantity' => 10,
            ]);

            $result = Product::query()->forSaleForm()->first();

            expect($result->getAttributes())->toHaveKey('stock_quantity')
                ->and($result->stock_quantity)->toBe(10);
        });
    });

    describe('forPurchaseForm', function (): void {
        it('exists as a method', function (): void {
            $builder = Product::query();

            expect(method_exists($builder, 'forPurchaseForm'))->toBeTrue();
        });

        it('returns EloquentCollection', function (): void {
            Product::factory()->count(3)->create();

            $result = Product::query()->forPurchaseForm();

            expect($result)->toBeInstanceOf(EloquentCollection::class);
        });

        it('returns collection of Product models', function (): void {
            Product::factory()->count(3)->create();

            $result = Product::query()->forPurchaseForm();

            expect($result->first())->toBeInstanceOf(Product::class);
        });

        it('eager loads unit relationship', function (): void {
            Product::factory()->create();

            $result = Product::query()->forPurchaseForm()->first();

            expect($result->relationLoaded('unit'))->toBeTrue();
        });

        it('selects required fields', function (): void {
            Product::factory()->create([
                'name' => 'Test Product',
                'sku' => 'TEST-001',
                'cost_price' => 1000,
            ]);

            $result = Product::query()->forPurchaseForm()->first();

            expect($result->name)->toBe('Test Product')
                ->and($result->sku)->toBe('TEST-001')
                ->and($result->cost_price)->toBe(1000);
        });
    });

    describe('forStockTransferForm', function (): void {
        it('exists as a method', function (): void {
            $builder = Product::query();

            expect(method_exists($builder, 'forStockTransferForm'))->toBeTrue();
        });

        it('returns EloquentCollection', function (): void {
            Product::factory()->count(3)->create();

            $result = Product::query()->forStockTransferForm();

            expect($result)->toBeInstanceOf(EloquentCollection::class);
        });

        it('returns collection of Product models', function (): void {
            Product::factory()->count(3)->create();

            $result = Product::query()->forStockTransferForm();

            expect($result->first())->toBeInstanceOf(Product::class);
        });

        it('eager loads unit relationship', function (): void {
            Product::factory()->create();

            $result = Product::query()->forStockTransferForm()->first();

            expect($result->relationLoaded('unit'))->toBeTrue();
        });

        it('eager loads batches with warehouse', function (): void {
            $product = Product::factory()->create();
            $warehouse = Warehouse::factory()->create();
            Batch::factory()->create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => 10,
            ]);

            $result = Product::query()->forStockTransferForm()->first();

            expect($result->relationLoaded('batches'))->toBeTrue();
            if ($result->batches->isNotEmpty()) {
                expect($result->batches->first()->relationLoaded('warehouse'))->toBeTrue();
            }
        });

        it('includes stock_quantity attribute', function (): void {
            $product = Product::factory()->create();
            Batch::factory()->create([
                'product_id' => $product->id,
                'quantity' => 15,
            ]);

            $result = Product::query()->forStockTransferForm()->first();

            expect($result->getAttributes())->toHaveKey('stock_quantity')
                ->and($result->stock_quantity)->toBe(15);
        });
    });

    describe('forPosSearch', function (): void {
        it('exists as a method', function (): void {
            $builder = Product::query();

            expect(method_exists($builder, 'forPosSearch'))->toBeTrue();
        });

        it('returns EloquentCollection', function (): void {
            Product::factory()->count(3)->create();

            $result = Product::query()->forPosSearch();

            expect($result)->toBeInstanceOf(EloquentCollection::class);
        });

        it('returns collection of Product models', function (): void {
            Product::factory()->count(3)->create();

            $result = Product::query()->forPosSearch();

            expect($result->first())->toBeInstanceOf(Product::class);
        });

        it('eager loads unit relationship', function (): void {
            Product::factory()->create();

            $result = Product::query()->forPosSearch()->first();

            expect($result->relationLoaded('unit'))->toBeTrue();
        });

        it('includes stock_quantity attribute', function (): void {
            $product = Product::factory()->create();
            Batch::factory()->create([
                'product_id' => $product->id,
                'quantity' => 20,
            ]);

            $result = Product::query()->forPosSearch()->first();

            expect($result->getAttributes())->toHaveKey('stock_quantity')
                ->and($result->stock_quantity)->toBe(20);
        });
    });

    describe('withStockQuantity', function (): void {
        it('exists as a method', function (): void {
            $builder = Product::query();

            expect(method_exists($builder, 'withStockQuantity'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Product::query();

            $result = $builder->withStockQuantity();

            expect($result)->toBe($builder);
        });
    });

    describe('search', function (): void {
        it('exists as a method', function (): void {
            $builder = Product::query();

            expect(method_exists($builder, 'search'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Product::query();

            $result = $builder->search('test');

            expect($result)->toBe($builder);
        });
    });

    describe('paginateWithFilters', function (): void {
        it('exists as a method', function (): void {
            $builder = Product::query();

            expect(method_exists($builder, 'paginateWithFilters'))->toBeTrue();
        });

        it('returns LengthAwarePaginator', function (): void {
            Product::factory()->count(5)->create();

            $result = Product::query()->paginateWithFilters([], 10);

            expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
        });

        it('returns paginated results', function (): void {
            Product::factory()->count(5)->create();

            $result = Product::query()->paginateWithFilters([], 2);

            expect($result)->toHaveCount(2)
                ->and($result->total())->toBe(5);
        });
    });
});
