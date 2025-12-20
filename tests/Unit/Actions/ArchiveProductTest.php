<?php

declare(strict_types=1);

use App\Actions\Products\ArchiveProduct;
use App\Actions\Products\RestoreProduct;
use App\Models\Product;

it('archives and restores a product using is_active flag', function (): void {
    $product = Product::factory()->create(['is_active' => true]);

    $archive = resolve(ArchiveProduct::class);
    $archive->handle($product);

    expect($product->refresh()->is_active)->toBeFalse();

    $restore = resolve(RestoreProduct::class);
    $restore->handle($product);

    expect($product->refresh()->is_active)->toBeTrue();
});
