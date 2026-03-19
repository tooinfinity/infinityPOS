<?php

declare(strict_types=1);

use App\Models\Batch;
use App\Models\Brand;
use App\Models\Category;
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

    describe('category', function (): void {
        it('exists as a method', function (): void {
            $builder = Product::query();

            expect(method_exists($builder, 'category'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Product::query();

            $result = $builder->category(1);

            expect($result)->toBe($builder);
        });

        it('filters by category id', function (): void {
            $category = Category::factory()->create();
            $product1 = Product::factory()->create(['category_id' => $category->id]);
            $product2 = Product::factory()->create(['category_id' => null]);

            $result = Product::query()->category($category->id)->get();

            expect($result)->toHaveCount(1)
                ->and($result->first()->id)->toBe($product1->id);
        });

        it('does not filter when category id is null', function (): void {
            Product::factory()->count(3)->create();

            $result = Product::query()->category(null)->get();

            expect($result)->toHaveCount(3);
        });
    });

    describe('brand', function (): void {
        it('exists as a method', function (): void {
            $builder = Product::query();

            expect(method_exists($builder, 'brand'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Product::query();

            $result = $builder->brand(1);

            expect($result)->toBe($builder);
        });

        it('filters by brand id', function (): void {
            $brand = Brand::factory()->create();
            $product1 = Product::factory()->create(['brand_id' => $brand->id]);
            $product2 = Product::factory()->create(['brand_id' => null]);

            $result = Product::query()->brand($brand->id)->get();

            expect($result)->toHaveCount(1)
                ->and($result->first()->id)->toBe($product1->id);
        });

        it('does not filter when brand id is null', function (): void {
            Product::factory()->count(3)->create();

            $result = Product::query()->brand(null)->get();

            expect($result)->toHaveCount(3);
        });
    });

    describe('tracked', function (): void {
        it('exists as a method', function (): void {
            $builder = Product::query();

            expect(method_exists($builder, 'tracked'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Product::query();

            $result = $builder->tracked(true);

            expect($result)->toBe($builder);
        });

        it('filters by track inventory true', function (): void {
            $product1 = Product::factory()->create(['track_inventory' => true]);
            $product2 = Product::factory()->create(['track_inventory' => false]);

            $result = Product::query()->tracked(true)->get();

            expect($result)->toHaveCount(1)
                ->and($result->first()->id)->toBe($product1->id);
        });

        it('filters by track inventory false', function (): void {
            $product1 = Product::factory()->create(['track_inventory' => true]);
            $product2 = Product::factory()->create(['track_inventory' => false]);

            $result = Product::query()->tracked(false)->get();

            expect($result)->toHaveCount(1)
                ->and($result->first()->id)->toBe($product2->id);
        });

        it('does not filter when tracked is null', function (): void {
            Product::factory()->create(['track_inventory' => true]);
            Product::factory()->create(['track_inventory' => false]);

            $result = Product::query()->tracked(null)->get();

            expect($result)->toHaveCount(2);
        });
    });

    describe('lowStock', function (): void {
        it('exists as a method', function (): void {
            $builder = Product::query();

            expect(method_exists($builder, 'lowStock'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Product::query();

            $result = $builder->lowStock();

            expect($result)->toBe($builder);
        });

        it('filters products with low stock', function (): void {
            $product = Product::factory()->create([
                'track_inventory' => true,
                'alert_quantity' => 10,
            ]);
            Batch::factory()->create([
                'product_id' => $product->id,
                'quantity' => 5,
            ]);

            $result = Product::query()->lowStock()->get();

            expect($result)->toHaveCount(1)
                ->and($result->first()->id)->toBe($product->id);
        });

        it('excludes products with sufficient stock', function (): void {
            $product = Product::factory()->create([
                'track_inventory' => true,
                'alert_quantity' => 10,
            ]);
            Batch::factory()->create([
                'product_id' => $product->id,
                'quantity' => 20,
            ]);

            $result = Product::query()->lowStock()->get();

            expect($result)->toBeEmpty();
        });

        it('excludes products without inventory tracking', function (): void {
            $product = Product::factory()->create([
                'track_inventory' => false,
                'alert_quantity' => 10,
            ]);
            Batch::factory()->create([
                'product_id' => $product->id,
                'quantity' => 5,
            ]);

            $result = Product::query()->lowStock()->get();

            expect($result)->toBeEmpty();
        });
    });

    describe('outOfStock', function (): void {
        it('exists as a method', function (): void {
            $builder = Product::query();

            expect(method_exists($builder, 'outOfStock'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Product::query();

            $result = $builder->outOfStock();

            expect($result)->toBe($builder);
        });

        it('filters products out of stock', function (): void {
            $product = Product::factory()->create([
                'track_inventory' => true,
            ]);
            Batch::factory()->create([
                'product_id' => $product->id,
                'quantity' => 0,
            ]);

            $result = Product::query()->outOfStock()->get();

            expect($result)->toHaveCount(1)
                ->and($result->first()->id)->toBe($product->id);
        });

        it('excludes products with stock', function (): void {
            $product = Product::factory()->create([
                'track_inventory' => true,
            ]);
            Batch::factory()->create([
                'product_id' => $product->id,
                'quantity' => 10,
            ]);

            $result = Product::query()->outOfStock()->get();

            expect($result)->toBeEmpty();
        });

        it('excludes products without inventory tracking', function (): void {
            $product = Product::factory()->create([
                'track_inventory' => false,
            ]);
            Batch::factory()->create([
                'product_id' => $product->id,
                'quantity' => 0,
            ]);

            $result = Product::query()->outOfStock()->get();

            expect($result)->toBeEmpty();
        });
    });

    describe('getStockByWarehouse', function (): void {
        it('exists as a method', function (): void {
            $builder = Product::query();

            expect(method_exists($builder, 'getStockByWarehouse'))->toBeTrue();
        });

        it('returns collection', function (): void {
            $product = Product::factory()->create();
            $warehouse = Warehouse::factory()->create();
            Batch::factory()->create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => 10,
            ]);

            $result = Product::query()->getStockByWarehouse();

            expect($result)->toBeInstanceOf(Illuminate\Support\Collection::class);
        });

        it('returns stock grouped by warehouse', function (): void {
            $product = Product::factory()->create();
            $warehouse1 = Warehouse::factory()->create(['name' => 'Warehouse A']);
            $warehouse2 = Warehouse::factory()->create(['name' => 'Warehouse B']);

            Batch::factory()->create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse1->id,
                'quantity' => 10,
            ]);

            Batch::factory()->create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse2->id,
                'quantity' => 20,
            ]);

            $result = Product::query()->getStockByWarehouse();

            expect($result)->toHaveCount(2);
        });
    });

    describe('getRecentMovements', function (): void {
        it('exists as a method', function (): void {
            $builder = Product::query();

            expect(method_exists($builder, 'getRecentMovements'))->toBeTrue();
        });

        it('returns collection', function (): void {
            $product = Product::factory()->create();
            App\Models\StockMovement::factory()->create([
                'product_id' => $product->id,
            ]);

            $result = Product::query()->getRecentMovements();

            expect($result)->toBeInstanceOf(Illuminate\Support\Collection::class);
        });

        it('returns limited movements', function (): void {
            $product = Product::factory()->create();
            App\Models\StockMovement::factory()->count(5)->create([
                'product_id' => $product->id,
            ]);

            $result = Product::query()->getRecentMovements(3);

            expect($result)->toHaveCount(3);
        });
    });

    describe('applyFilters', function (): void {
        it('exists as a method', function (): void {
            $builder = Product::query();

            expect(method_exists($builder, 'applyFilters'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Product::query();

            $result = $builder->applyFilters([]);

            expect($result)->toBe($builder);
        });

        it('applies search filter', function (): void {
            Product::factory()->create(['name' => 'Test Product']);
            Product::factory()->create(['name' => 'Other Product']);

            $result = Product::query()->applyFilters(['search' => 'Test'])->get();

            expect($result)->toHaveCount(1)
                ->and($result->first()->name)->toBe('Test Product');
        });

        it('applies category filter', function (): void {
            $category = Category::factory()->create();
            $product1 = Product::factory()->create(['category_id' => $category->id]);
            $product2 = Product::factory()->create(['category_id' => null]);

            $result = Product::query()->applyFilters(['category_id' => $category->id])->get();

            expect($result)->toHaveCount(1)
                ->and($result->first()->id)->toBe($product1->id);
        });

        it('applies brand filter', function (): void {
            $brand = Brand::factory()->create();
            $product1 = Product::factory()->create(['brand_id' => $brand->id]);
            $product2 = Product::factory()->create(['brand_id' => null]);

            $result = Product::query()->applyFilters(['brand_id' => $brand->id])->get();

            expect($result)->toHaveCount(1)
                ->and($result->first()->id)->toBe($product1->id);
        });

        it('applies track inventory filter', function (): void {
            $product1 = Product::factory()->create(['track_inventory' => true]);
            $product2 = Product::factory()->create(['track_inventory' => false]);

            $result = Product::query()->applyFilters(['track_inventory' => 'true'])->get();

            expect($result)->toHaveCount(1)
                ->and($result->first()->id)->toBe($product1->id);
        });

        it('applies out of stock filter', function (): void {
            $product = Product::factory()->create(['track_inventory' => true]);
            Batch::factory()->create([
                'product_id' => $product->id,
                'quantity' => 0,
            ]);

            $result = Product::query()->applyFilters(['out_of_stock' => 'true'])->get();

            expect($result)->toHaveCount(1);
        });

        it('applies low stock filter', function (): void {
            $product = Product::factory()->create([
                'track_inventory' => true,
                'alert_quantity' => 10,
            ]);
            Batch::factory()->create([
                'product_id' => $product->id,
                'quantity' => 5,
            ]);

            $result = Product::query()->applyFilters(['low_stock' => 'true'])->get();

            expect($result)->toHaveCount(1);
        });

        it('applies sorting', function (): void {
            $product1 = Product::factory()->create(['name' => 'A Product']);
            $product2 = Product::factory()->create(['name' => 'B Product']);

            $result = Product::query()->applyFilters(['sort' => 'name', 'direction' => 'asc'])->get();

            expect($result->first()->name)->toBe('A Product');
        });
    });
});
