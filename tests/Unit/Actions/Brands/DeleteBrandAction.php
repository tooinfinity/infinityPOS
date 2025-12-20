<?php

declare(strict_types=1);

use App\Actions\Brands\DeleteBrand;
use App\Models\Brand;
use App\Models\User;

it('may delete a brand', function (): void {
    $user = User::factory()->create();
    $brand = Brand::factory()->create(['created_by' => $user->id]);

    $action = resolve(DeleteBrand::class);
    $action->handle($brand);

    expect(Brand::query()->find($brand->id))->toBeNull()
        ->and($brand->created_by)->toBeNull();
});
