<?php

declare(strict_types=1);

use App\Actions\Brands\CreateBrand;
use App\Data\Brands\CreateBrandData;
use App\Models\Brand;
use App\Models\User;

it('may create a brand', function (): void {
    $user = User::factory()->create();
    $action = resolve(CreateBrand::class);

    $data = CreateBrandData::from([
        'name' => 'Test Brand',
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    $brand = $action->handle($data);

    expect($brand)->toBeInstanceOf(Brand::class)
        ->and($brand->name)->toBe('Test Brand')
        ->and($brand->is_active)->toBeTrue()
        ->and($brand->created_by)->toBe($user->id);
});
