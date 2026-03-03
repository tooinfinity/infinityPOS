<?php

declare(strict_types=1);

use App\Data\Unit\CreateUnitData;

it('may be created with required fields', function (): void {
    $data = new CreateUnitData(
        name: 'Piece',
        short_name: 'pc',
        is_active: true,
    );

    expect($data)
        ->name->toBe('Piece')
        ->short_name->toBe('pc')
        ->is_active->toBeTrue();
});
