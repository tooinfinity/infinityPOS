<?php

declare(strict_types=1);

use App\Data\Batch\UpdateBatchData;
use Spatie\LaravelData\Optional;

it('may be created with optional fields', function (): void {
    $data = new UpdateBatchData(
        batch_number: Optional::create(),
        cost_amount: Optional::create(),
        quantity: Optional::create(),
        expires_at: Optional::create(),
    );

    expect($data->batch_number)->toBeInstanceOf(Optional::class);
});

it('may be created with specific values', function (): void {
    $expiresAt = Illuminate\Support\Facades\Date::now()->addYear();

    $data = new UpdateBatchData(
        batch_number: 'NEW-BATCH',
        cost_amount: 6000,
        quantity: 200,
        expires_at: $expiresAt,
    );

    expect($data->batch_number)->toBe('NEW-BATCH')
        ->and($data->cost_amount)->toBe(6000)
        ->and($data->quantity)->toBe(200)
        ->and($data->expires_at)->toBe($expiresAt);
});
