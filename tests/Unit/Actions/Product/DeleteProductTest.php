<?php

declare(strict_types=1);

use App\Actions\Product\DeleteProduct;
use App\Models\Batch;
use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturnItem;
use App\Models\SaleItem;
use App\Models\SaleReturnItem;
use App\Models\StockMovement;
use App\Models\StockTransferItem;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
});

it('may delete a product with no related records', function (): void {
    $product = Product::factory()->create();

    $action = resolve(DeleteProduct::class);

    $result = $action->handle($product);

    expect($result)->toBeTrue()
        ->and($product->exists)->toBeFalse();
});

it('throws exception when product has batches', function (): void {
    $product = Product::factory()->create();
    Batch::factory()->create(['product_id' => $product->id]);

    $action = resolve(DeleteProduct::class);

    expect(fn () => $action->handle($product))
        ->toThrow(RuntimeException::class, 'Cannot delete product with existing batches');
});

it('throws exception when product has stock movements', function (): void {
    $product = Product::factory()->create();
    StockMovement::factory()->create(['product_id' => $product->id]);

    $action = resolve(DeleteProduct::class);

    expect(fn () => $action->handle($product))
        ->toThrow(RuntimeException::class, 'Cannot delete product with existing stockMovements');
});

it('throws exception when product has purchase items', function (): void {
    $product = Product::factory()->create();
    PurchaseItem::factory()->create(['product_id' => $product->id]);

    $action = resolve(DeleteProduct::class);

    expect(fn () => $action->handle($product))
        ->toThrow(RuntimeException::class, 'Cannot delete product with existing purchaseItems');
});

it('throws exception when product has sale items', function (): void {
    $product = Product::factory()->create();
    SaleItem::factory()->create(['product_id' => $product->id]);

    $action = resolve(DeleteProduct::class);

    expect(fn () => $action->handle($product))
        ->toThrow(RuntimeException::class, 'Cannot delete product with existing saleItems');
});

it('throws exception when product has stock transfer items', function (): void {
    $product = Product::factory()->create();
    StockTransferItem::factory()->create(['product_id' => $product->id]);

    $action = resolve(DeleteProduct::class);

    expect(fn () => $action->handle($product))
        ->toThrow(RuntimeException::class, 'Cannot delete product with existing stockTransferItems');
});

it('throws exception when product has sale return items', function (): void {
    $product = Product::factory()->create();
    SaleReturnItem::factory()->create(['product_id' => $product->id]);

    $action = resolve(DeleteProduct::class);

    expect(fn () => $action->handle($product))
        ->toThrow(RuntimeException::class, 'Cannot delete product with existing saleReturnItems');
});

it('throws exception when product has purchase return items', function (): void {
    $product = Product::factory()->create();
    PurchaseReturnItem::factory()->create(['product_id' => $product->id]);

    $action = resolve(DeleteProduct::class);

    expect(fn () => $action->handle($product))
        ->toThrow(RuntimeException::class, 'Cannot delete product with existing purchaseReturnItems');
});

it('includes all related record types in exception message', function (): void {
    $product = Product::factory()->create();
    Batch::factory()->create(['product_id' => $product->id]);
    PurchaseItem::factory()->create(['product_id' => $product->id]);

    $action = resolve(DeleteProduct::class);

    expect(fn () => $action->handle($product))
        ->toThrow(function (RuntimeException $e): void {
            expect($e->getMessage())->toContain('batches')
                ->and($e->getMessage())->toContain('purchaseItems');
        });
});

it('deletes product image when deleting product', function (): void {
    Storage::disk('public')->put('products/test-image.jpg', 'fake-content');

    $product = Product::factory()->create([
        'image' => 'products/test-image.jpg',
    ]);

    expect(Storage::disk('public')->exists('products/test-image.jpg'))->toBeTrue();

    $action = resolve(DeleteProduct::class);
    $action->handle($product);

    expect(Storage::disk('public')->exists('products/test-image.jpg'))->toBeFalse();
});

it('deletes product without image', function (): void {
    $product = Product::factory()->create([
        'image' => null,
    ]);

    $action = resolve(DeleteProduct::class);

    $result = $action->handle($product);

    expect($result)->toBeTrue()
        ->and(Product::query()->find($product->id))->toBeNull();
});

it('does not delete product when any related record exists', function (): void {
    $product = Product::factory()->create();
    Batch::factory()->create(['product_id' => $product->id]);

    $action = resolve(DeleteProduct::class);

    try {
        $action->handle($product);
        test()->fail('Expected RuntimeException to be thrown');
    } catch (RuntimeException) {
        // Expected exception
    }

    expect(Product::query()->find($product->id))->not->toBeNull();
});

it('rolls back transaction when exception is thrown', function (): void {
    Storage::disk('public')->put('products/test-image.jpg', 'fake-content');

    $product = Product::factory()->create([
        'image' => 'products/test-image.jpg',
    ]);
    Batch::factory()->create(['product_id' => $product->id]);

    $action = resolve(DeleteProduct::class);

    try {
        $action->handle($product);
        test()->fail('Expected RuntimeException to be thrown');
    } catch (RuntimeException) {
        // Expected exception
    }

    expect(Product::query()->find($product->id))->not->toBeNull()
        ->and(Storage::disk('public')->exists('products/test-image.jpg'))->toBeTrue();
});

it('prevents deletion when multiple related records exist', function (): void {
    $product = Product::factory()->create();
    Batch::factory()->count(3)->create(['product_id' => $product->id]);
    StockMovement::factory()->count(2)->create(['product_id' => $product->id]);

    $action = resolve(DeleteProduct::class);

    expect(fn () => $action->handle($product))
        ->toThrow(RuntimeException::class);
});
