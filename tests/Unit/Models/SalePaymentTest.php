<?php

declare(strict_types=1);

use App\Models\SalePayment;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

test('to array', function (): void {
    $salePayment = SalePayment::factory()->create()->refresh();

    expect(array_keys($salePayment->toArray()))
        ->toBe([
            'id',
            'sale_id',
            'payment_method',
            'amount',
            'reference_number',
            'created_at',
        ]);
});

test('sale relationship returns belongs to', function (): void {
    $salePayment = new SalePayment();

    expect($salePayment->sale())
        ->toBeInstanceOf(BelongsTo::class);
});

test('casts returns correct array', function (): void {
    $salePayment = new SalePayment();

    expect($salePayment->casts())
        ->toBe([
            'id' => 'integer',
            'sale_id' => 'integer',
            'payment_method' => App\Enums\PaymentMethodEnum::class,
            'amount' => 'integer',
            'reference_number' => 'string',
            'created_at' => 'datetime',
        ]);
});

test('casts work correctly', function (): void {
    $salePayment = SalePayment::factory()->create()->refresh();

    expect($salePayment->id)->toBeInt()
        ->and($salePayment->amount)->toBeInt()
        ->and($salePayment->created_at)->toBeInstanceOf(DateTimeInterface::class);
});

test('casts payment_method to PaymentMethodEnum', function (): void {
    $salePayment = SalePayment::factory()->create([
        'payment_method' => App\Enums\PaymentMethodEnum::CARD,
    ]);

    expect($salePayment->payment_method)->toBeInstanceOf(App\Enums\PaymentMethodEnum::class)
        ->and($salePayment->payment_method)->toBe(App\Enums\PaymentMethodEnum::CARD);
});

test('can set payment_method using enum value', function (): void {
    $salePayment = SalePayment::factory()->create([
        'payment_method' => 'cash',
    ]);

    expect($salePayment->payment_method)->toBeInstanceOf(App\Enums\PaymentMethodEnum::class)
        ->and($salePayment->payment_method->value)->toBe('cash');
});

test('can access enum methods on payment_method', function (): void {
    $salePayment = SalePayment::factory()->create([
        'payment_method' => App\Enums\PaymentMethodEnum::CARD,
    ]);

    expect($salePayment->payment_method->label())->toBe('Card')
        ->and($salePayment->payment_method->color())->toBeString()
        ->and($salePayment->payment_method->icon())->toBeString()
        ->and($salePayment->payment_method->isCash())->toBeFalse();
});
