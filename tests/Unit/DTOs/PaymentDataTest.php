<?php

declare(strict_types=1);

use App\DTOs\PaymentData;
use App\Enums\PaymentMethodEnum;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Exceptions\CannotCreateData;

it('creates payment DTO from array', function (): void {
    $data = PaymentData::from([
        'payment_method' => 'cash',
        'amount' => 5000,
        'reference_number' => 'REF-123',
        'payment_date' => '2024-01-15 10:30:00',
        'notes' => 'Payment received',
    ]);

    expect($data->paymentMethod)->toBeInstanceOf(PaymentMethodEnum::class)
        ->and($data->paymentMethod)->toBe(PaymentMethodEnum::CASH)
        ->and($data->amount)->toBe(5000)
        ->and($data->referenceNumber)->toBe('REF-123')
        ->and($data->paymentDate)->toBe('2024-01-15 10:30:00')
        ->and($data->notes)->toBe('Payment received');
});

it('creates payment DTO with minimal data', function (): void {
    $data = PaymentData::from([
        'payment_method' => 'card',
        'amount' => 1000,
    ]);

    expect($data->paymentMethod)->toBe(PaymentMethodEnum::CARD)
        ->and($data->amount)->toBe(1000)
        ->and($data->referenceNumber)->toBeNull()
        ->and($data->paymentDate)->toBeNull()
        ->and($data->notes)->toBeNull();
});

it('validates required fields', function (): void {
    PaymentData::from([
        'amount' => 1000,
    ]);
})->throws(CannotCreateData::class);

it('validates amount is non-negative', function (): void {
    PaymentData::validateAndCreate([
        'payment_method' => 'cash',
        'amount' => -500,
    ]);
})->throws(ValidationException::class);

it('validates payment method with enum', function (): void {
    $data = PaymentData::from([
        'payment_method' => 'bank_transfer',
        'amount' => 2500,
    ]);

    expect($data->paymentMethod)->toBeInstanceOf(PaymentMethodEnum::class)
        ->and($data->paymentMethod)->toBe(PaymentMethodEnum::BANK_TRANSFER);
});

it('accepts enum case values for payment method', function (): void {
    $data = PaymentData::from([
        'payment_method' => PaymentMethodEnum::CHECK->value,
        'amount' => 1500,
    ]);

    expect($data->paymentMethod)->toBe(PaymentMethodEnum::CHECK);
});

it('can use enum directly for payment method', function (): void {
    $data = PaymentData::from([
        'payment_method' => PaymentMethodEnum::SPLIT,
        'amount' => 3000,
    ]);

    expect($data->paymentMethod)->toBe(PaymentMethodEnum::SPLIT);
});

it('rejects invalid payment method', function (): void {
    PaymentData::validateAndCreate([
        'payment_method' => 'invalid-method',
        'amount' => 1000,
    ]);
})->throws(ValidationException::class);

it('handles snake_case mapping', function (): void {
    $data = PaymentData::validateAndCreate([
        'payment_method' => 'bank_transfer',
        'amount' => 2500,
        'reference_number' => 'REF-456',
        'payment_date' => '2024-02-01',
    ]);

    expect($data->paymentMethod)->toBe(PaymentMethodEnum::BANK_TRANSFER)
        ->and($data->referenceNumber)->toBe('REF-456')
        ->and($data->paymentDate)->toBe('2024-02-01');
});
