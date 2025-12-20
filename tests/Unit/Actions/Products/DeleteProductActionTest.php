<?php

declare(strict_types=1);

use App\Actions\Products\DeleteProduct;
use App\Models\Product;
use App\Models\User;

it('may delete a product', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);

    $action = resolve(DeleteProduct::class);
    $action->handle($product);

    expect(Product::query()->find($product->id))->toBeNull()
        ->and($product->created_by)->toBeNull();
});
