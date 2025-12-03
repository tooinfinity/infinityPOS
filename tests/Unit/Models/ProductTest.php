<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();

    $product = Product::factory()->create(['created_by' => $user->id])->refresh();

    expect(array_keys($product->toArray()))
        ->toBe([
            'id',
            'sku',
            'barcode',
            'name',
            'description',
            'image',
            'cost',
            'price',
            'alert_quantity',
            'has_batches',
            'is_active',
            'category_id',
            'brand_id',
            'unit_id',
            'tax_id',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});
