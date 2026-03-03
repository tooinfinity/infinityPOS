<?php

declare(strict_types=1);

use App\Data\Batch\CreateBatchData;

it('may be created with required fields', function (): void {
    $data = new CreateBatchData(
        product_id: 1,
        warehouse_id: 1,
        batch_number: 'BATCH-001',
        cost_amount: 5000,
        quantity: 100,
        expires_at: null,
    );

    expect($data)
        ->product_id->toBe(1)
        ->warehouse_id->toBe(1)
        ->batch_number->toBe('BATCH-001')
        ->cost_amount->toBe(5000)
        ->quantity->toBe(100)
        ->expires_at->toBeNull();
});

it('may be created with expiration date', function (): void {
    $expiresAt = Illuminate\Support\Facades\Date::now()->addYear();

    $data = new CreateBatchData(
        product_id: 1,
        warehouse_id: 1,
        batch_number: 'BATCH-001',
        cost_amount: 5000,
        quantity: 100,
        expires_at: $expiresAt,
    );

    expect($data->expires_at)->toBe($expiresAt);
});
