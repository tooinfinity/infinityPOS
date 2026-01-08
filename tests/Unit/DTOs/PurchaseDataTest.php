<?php

declare(strict_types=1);

use App\DTOs\PurchaseData;
use App\DTOs\PurchaseItemData;
use App\Enums\PaymentMethodEnum;
use App\Enums\PurchaseStatusEnum;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\DataCollection;

it('creates purchase DTO from array with items', function (): void {
    $data = PurchaseData::from([
        'store_id' => 1,
        'supplier_id' => 5,
        'purchase_date' => '2024-01-15 10:00:00',
        'total_amount' => 5000,
        'payment_status' => 'completed',
        'payment_method' => 'bank_transfer',
        'reference_number' => 'PO-001',
        'items' => [
            ['product_id' => 1, 'quantity' => 10, 'unit_cost' => 500],
        ],
        'notes' => 'First purchase',
    ]);

    expect($data->storeId)->toBe(1)
        ->and($data->supplierId)->toBe(5)
        ->and($data->purchaseDate)->toBe('2024-01-15 10:00:00')
        ->and($data->totalAmount)->toBe(5000)
        ->and($data->paymentStatus)->toBeInstanceOf(PurchaseStatusEnum::class)
        ->and($data->paymentStatus)->toBe(PurchaseStatusEnum::COMPLETED)
        ->and($data->paymentMethod)->toBeInstanceOf(PaymentMethodEnum::class)
        ->and($data->paymentMethod)->toBe(PaymentMethodEnum::BANK_TRANSFER)
        ->and($data->referenceNumber)->toBe('PO-001')
        ->and($data->items)->toBeInstanceOf(DataCollection::class)
        ->and($data->items->count())->toBe(1)
        ->and($data->items->first())->toBeInstanceOf(PurchaseItemData::class)
        ->and($data->notes)->toBe('First purchase');
});

it('creates purchase DTO with default payment status', function (): void {
    $data = PurchaseData::from([
        'store_id' => 1,
        'supplier_id' => 5,
        'purchase_date' => '2024-01-15',
        'total_amount' => 3000,
        'items' => [],
    ]);

    expect($data->paymentStatus)->toBe(PurchaseStatusEnum::PENDING)
        ->and($data->paymentMethod)->toBeNull()
        ->and($data->referenceNumber)->toBeNull()
        ->and($data->notes)->toBeNull();
});

it('calculates total from items', function (): void {
    $data = PurchaseData::from([
        'store_id' => 1,
        'supplier_id' => 5,
        'purchase_date' => '2024-01-15',
        'total_amount' => 1500,
        'items' => [
            ['product_id' => 1, 'quantity' => 5, 'unit_cost' => 100],
            ['product_id' => 2, 'quantity' => 10, 'unit_cost' => 100],
        ],
    ]);

    expect($data->calculateTotal())->toBe(1500);
});

it('calculates total with empty items', function (): void {
    $data = PurchaseData::from([
        'store_id' => 1,
        'supplier_id' => 5,
        'purchase_date' => '2024-01-15',
        'total_amount' => 0,
        'items' => [],
    ]);

    expect($data->calculateTotal())->toBe(0);
});

it('validates payment status with enum', function (): void {
    $data = PurchaseData::from([
        'store_id' => 1,
        'supplier_id' => 5,
        'purchase_date' => '2024-01-15',
        'total_amount' => 1000,
        'payment_status' => 'pending',
        'items' => [],
    ]);

    expect($data->paymentStatus)->toBeInstanceOf(PurchaseStatusEnum::class)
        ->and($data->paymentStatus)->toBe(PurchaseStatusEnum::PENDING);
});

it('accepts enum case values for payment status', function (): void {
    $data = PurchaseData::from([
        'store_id' => 1,
        'supplier_id' => 5,
        'purchase_date' => '2024-01-15',
        'total_amount' => 1000,
        'payment_status' => PurchaseStatusEnum::COMPLETED->value,
        'items' => [],
    ]);

    expect($data->paymentStatus)->toBe(PurchaseStatusEnum::COMPLETED);
});

it('can use enum directly for payment status', function (): void {
    $data = PurchaseData::from([
        'store_id' => 1,
        'supplier_id' => 5,
        'purchase_date' => '2024-01-15',
        'total_amount' => 1000,
        'payment_status' => PurchaseStatusEnum::CANCELLED,
        'items' => [],
    ]);

    expect($data->paymentStatus)->toBe(PurchaseStatusEnum::CANCELLED);
});

it('rejects invalid payment status', function (): void {
    PurchaseData::validateAndCreate([
        'store_id' => 1,
        'supplier_id' => 5,
        'purchase_date' => '2024-01-15',
        'total_amount' => 1000,
        'payment_status' => 'invalid',
        'items' => [],
    ]);
})->throws(ValidationException::class);

it('validates payment method with enum', function (): void {
    $data = PurchaseData::from([
        'store_id' => 1,
        'supplier_id' => 5,
        'purchase_date' => '2024-01-15',
        'total_amount' => 1000,
        'payment_method' => 'cash',
        'items' => [],
    ]);

    expect($data->paymentMethod)->toBeInstanceOf(PaymentMethodEnum::class)
        ->and($data->paymentMethod)->toBe(PaymentMethodEnum::CASH);
});

it('accepts enum case values for payment method', function (): void {
    $data = PurchaseData::from([
        'store_id' => 1,
        'supplier_id' => 5,
        'purchase_date' => '2024-01-15',
        'total_amount' => 1000,
        'payment_method' => PaymentMethodEnum::CHECK->value,
        'items' => [],
    ]);

    expect($data->paymentMethod)->toBe(PaymentMethodEnum::CHECK);
});

it('can use enum directly for payment method', function (): void {
    $data = PurchaseData::from([
        'store_id' => 1,
        'supplier_id' => 5,
        'purchase_date' => '2024-01-15',
        'total_amount' => 1000,
        'payment_method' => PaymentMethodEnum::CARD,
        'items' => [],
    ]);

    expect($data->paymentMethod)->toBe(PaymentMethodEnum::CARD);
});

it('rejects invalid payment method', function (): void {
    PurchaseData::validateAndCreate([
        'store_id' => 1,
        'supplier_id' => 5,
        'purchase_date' => '2024-01-15',
        'total_amount' => 1000,
        'payment_method' => 'invalid-method',
        'items' => [],
    ]);
})->throws(ValidationException::class);

it('validates required fields', function (): void {
    PurchaseData::validateAndCreate([
        'store_id' => 1,
        'supplier_id' => 5,
        'items' => [],
    ]);
})->throws(ValidationException::class);

it('handles multiple purchase items', function (): void {
    $data = PurchaseData::from([
        'store_id' => 1,
        'supplier_id' => 5,
        'purchase_date' => '2024-01-15',
        'total_amount' => 2000,
        'items' => [
            ['product_id' => 1, 'quantity' => 10, 'unit_cost' => 50],
            ['product_id' => 2, 'quantity' => 5, 'unit_cost' => 100],
            ['product_id' => 3, 'quantity' => 20, 'unit_cost' => 50],
        ],
    ]);

    expect($data->items->count())->toBe(3)
        ->and($data->calculateTotal())->toBe(2000);
});
