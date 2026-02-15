<?php

declare(strict_types=1);

use App\Data\Unit\UpdateUnitData;
use Spatie\LaravelData\Optional;

it('may be created with optional fields', function (): void {
    $data = new UpdateUnitData(
        name: Optional::create(),
        short_name: Optional::create(),
        is_active: Optional::create(),
    );

    expect($data->name)->toBeInstanceOf(Optional::class);
});
