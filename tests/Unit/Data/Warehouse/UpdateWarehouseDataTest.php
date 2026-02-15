<?php

declare(strict_types=1);

use App\Data\Warehouse\UpdateWarehouseData;
use Spatie\LaravelData\Optional;

it('may be created with optional fields', function (): void {
    $data = new UpdateWarehouseData(
        name: Optional::create(),
        code: Optional::create(),
        email: Optional::create(),
        phone: Optional::create(),
        address: Optional::create(),
        city: Optional::create(),
        country: Optional::create(),
        is_active: Optional::create(),
    );

    expect($data->name)->toBeInstanceOf(Optional::class);
});
