<?php

declare(strict_types=1);

use App\DTOs\SaleData;
use App\DTOs\SaleItemData;
use App\Enums\PaymentMethodEnum;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Exceptions\CannotCreateData;

it('creates sale DTO from array with items', function (): void {
    $data = SaleData::from([
        'store_id' => 1,
        'customer_id' => 5,
        'register_session_id' => 10,
        'subtotal' => 1000,
        'discount_amount' => 100,
        'total_amount' => 900,
        'payment_method' => 'cash',
        'amount_paid' => 1000,
        'items' => [
            ['product_id' => 1, 'quantity' => 2, 'unit_price' => 500],
        ],
        'notes' => 'Customer paid cash',
        'cashier_id' => 3,
    ]);

    expect($data->storeId)->toBe(1)
        ->and($data->customerId)->toBe(5)
        ->and($data->registerSessionId)->toBe(10)
        ->and($data->subtotal)->toBe(1000)
        ->and($data->discountAmount)->toBe(100)
        ->and($data->totalAmount)->toBe(900)
        ->and($data->paymentMethod)->toBeInstanceOf(PaymentMethodEnum::class)
        ->and($data->paymentMethod)->toBe(PaymentMethodEnum::CASH)
        ->and($data->amountPaid)->toBe(1000)
        ->and($data->items)->toBeInstanceOf(DataCollection::class)
        ->and($data->items->count())->toBe(1)
        ->and($data->items->first())->toBeInstanceOf(SaleItemData::class)
        ->and($data->notes)->toBe('Customer paid cash')
        ->and($data->cashierId)->toBe(3);
});

it('creates sale DTO without optional fields', function (): void {
    $data = SaleData::from([
        'store_id' => 1,
        'customer_id' => null,
        'register_session_id' => null,
        'subtotal' => 500,
        'discount_amount' => 0,
        'total_amount' => 500,
        'payment_method' => 'card',
        'amount_paid' => 500,
        'items' => [],
    ]);

    expect($data->customerId)->toBeNull()
        ->and($data->registerSessionId)->toBeNull()
        ->and($data->discountAmount)->toBe(0)
        ->and($data->splitPayments)->toBeNull()
        ->and($data->notes)->toBeNull()
        ->and($data->cashierId)->toBeNull();
});

it('calculates change given correctly when overpaid', function (): void {
    $data = SaleData::from([
        'store_id' => 1,
        'customer_id' => null,
        'register_session_id' => null,
        'subtotal' => 750,
        'discount_amount' => 0,
        'total_amount' => 750,
        'payment_method' => 'cash',
        'amount_paid' => 1000,
        'items' => [],
    ]);

    expect($data->changeGiven())->toBe(250);
});

it('calculates zero change when exact amount paid', function (): void {
    $data = SaleData::from([
        'store_id' => 1,
        'customer_id' => null,
        'register_session_id' => null,
        'subtotal' => 500,
        'discount_amount' => 0,
        'total_amount' => 500,
        'payment_method' => 'cash',
        'amount_paid' => 500,
        'items' => [],
    ]);

    expect($data->changeGiven())->toBe(0);
});

it('calculates zero change when underpaid', function (): void {
    $data = SaleData::from([
        'store_id' => 1,
        'customer_id' => null,
        'register_session_id' => null,
        'subtotal' => 1000,
        'discount_amount' => 0,
        'total_amount' => 1000,
        'payment_method' => 'card',
        'amount_paid' => 500,
        'items' => [],
    ]);

    expect($data->changeGiven())->toBe(0);
});

it('handles multiple sale items', function (): void {
    $data = SaleData::from([
        'store_id' => 1,
        'customer_id' => null,
        'register_session_id' => null,
        'subtotal' => 1500,
        'discount_amount' => 0,
        'total_amount' => 1500,
        'payment_method' => 'cash',
        'amount_paid' => 1500,
        'items' => [
            ['product_id' => 1, 'quantity' => 2, 'unit_price' => 500],
            ['product_id' => 2, 'quantity' => 1, 'unit_price' => 500, 'unit_cost' => 300],
        ],
    ]);

    expect($data->items->count())->toBe(2)
        ->and($data->items->first()->productId)->toBe(1)
        ->and($data->items->last()->productId)->toBe(2)
        ->and($data->items->last()->unitCost)->toBe(300);
});

it('validates required fields', function (): void {
    SaleData::from([
        'subtotal' => 100,
        'items' => [],
    ]);
})->throws(CannotCreateData::class);

it('handles split payments', function (): void {
    $data = SaleData::from([
        'store_id' => 1,
        'customer_id' => null,
        'register_session_id' => null,
        'subtotal' => 1000,
        'discount_amount' => 0,
        'total_amount' => 1000,
        'payment_method' => 'split',
        'amount_paid' => 1000,
        'items' => [],
        'split_payments' => [
            ['method' => 'cash', 'amount' => 500],
            ['method' => 'card', 'amount' => 500],
        ],
    ]);

    expect($data->splitPayments)->toBeArray()
        ->and($data->splitPayments)->toHaveCount(2);
});

it('validates payment method with enum', function (): void {
    $data = SaleData::from([
        'store_id' => 1,
        'customer_id' => null,
        'register_session_id' => null,
        'subtotal' => 500,
        'total_amount' => 500,
        'payment_method' => 'card',
        'amount_paid' => 500,
        'items' => [],
    ]);

    expect($data->paymentMethod)->toBeInstanceOf(PaymentMethodEnum::class)
        ->and($data->paymentMethod)->toBe(PaymentMethodEnum::CARD);
});

it('accepts enum case values for payment method', function (): void {
    $data = SaleData::from([
        'store_id' => 1,
        'customer_id' => null,
        'register_session_id' => null,
        'subtotal' => 500,
        'total_amount' => 500,
        'payment_method' => PaymentMethodEnum::BANK_TRANSFER->value,
        'amount_paid' => 500,
        'items' => [],
    ]);

    expect($data->paymentMethod)->toBe(PaymentMethodEnum::BANK_TRANSFER);
});

it('can use enum directly for payment method', function (): void {
    $data = SaleData::from([
        'store_id' => 1,
        'customer_id' => null,
        'register_session_id' => null,
        'subtotal' => 500,
        'total_amount' => 500,
        'payment_method' => PaymentMethodEnum::CHECK,
        'amount_paid' => 500,
        'items' => [],
    ]);

    expect($data->paymentMethod)->toBe(PaymentMethodEnum::CHECK);
});

it('rejects invalid payment method', function (): void {
    SaleData::validateAndCreate([
        'store_id' => 1,
        'customer_id' => null,
        'register_session_id' => null,
        'subtotal' => 500,
        'total_amount' => 500,
        'payment_method' => 'invalid-method',
        'amount_paid' => 500,
        'items' => [],
    ]);
})->throws(ValidationException::class);

it('uses default payment method', function (): void {
    $data = SaleData::from([
        'store_id' => 1,
        'customer_id' => null,
        'register_session_id' => null,
        'subtotal' => 500,
        'total_amount' => 500,
        'amount_paid' => 500,
        'items' => [],
    ]);

    expect($data->paymentMethod)->toBe(PaymentMethodEnum::CASH);
});
