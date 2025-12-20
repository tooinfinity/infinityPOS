<?php

declare(strict_types=1);

use App\Actions\Brands\UpdateBrand;
use App\Data\Brands\UpdateBrandData;
use App\Models\Brand;
use App\Models\User;

it('may update a brand', function (): void {
    $user = User::factory()->create();
    $brand = Brand::factory()->create([
        'name' => 'Old Brand',
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    $user2 = User::factory()->create();
    $action = resolve(UpdateBrand::class);

    $data = UpdateBrandData::from([
        'name' => 'Test Brand',
        'is_active' => true,
        'updated_by' => $user2->id,
    ]);

    $action->handle($brand, $data);

    expect($brand->refresh()->name)->toBe('Test Brand')
        ->and($brand->is_active)->toBeTrue()
        ->and($brand->updated_by)->toBe($user2->id);
});
