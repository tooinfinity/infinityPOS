<?php

declare(strict_types=1);

use App\Models\SaleReturn;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

test('to array', function (): void {
    $saleReturn = SaleReturn::factory()->create()->refresh();

    expect(array_keys($saleReturn->toArray()))
        ->toBe([
            'id',
            'sale_id',
            'invoice_id',
            'store_id',
            'customer_id',
            'return_number',
            'return_date',
            'total_amount',
            'refund_method',
            'reason',
            'processed_by',
            'created_at',
            'updated_at',
        ]);
});

test('sale relationship returns belongs to', function (): void {
    $saleReturn = new SaleReturn();

    expect($saleReturn->sale())
        ->toBeInstanceOf(BelongsTo::class);
});

test('invoice relationship returns belongs to', function (): void {
    $saleReturn = new SaleReturn();

    expect($saleReturn->invoice())
        ->toBeInstanceOf(BelongsTo::class);
});

test('store relationship returns belongs to', function (): void {
    $saleReturn = new SaleReturn();

    expect($saleReturn->store())
        ->toBeInstanceOf(BelongsTo::class);
});

test('customer relationship returns belongs to', function (): void {
    $saleReturn = new SaleReturn();

    expect($saleReturn->customer())
        ->toBeInstanceOf(BelongsTo::class);
});

test('processor relationship returns belongs to', function (): void {
    $saleReturn = new SaleReturn();

    expect($saleReturn->processor())
        ->toBeInstanceOf(BelongsTo::class);
});

test('items relationship returns has many', function (): void {
    $saleReturn = new SaleReturn();

    expect($saleReturn->items())
        ->toBeInstanceOf(HasMany::class);
});

test('casts returns correct array', function (): void {
    $saleReturn = new SaleReturn();

    expect($saleReturn->casts())
        ->toBe([
            'id' => 'integer',
            'sale_id' => 'integer',
            'invoice_id' => 'integer',
            'store_id' => 'integer',
            'customer_id' => 'integer',
            'return_number' => 'string',
            'return_date' => 'datetime',
            'total_amount' => 'integer',
            'refund_method' => App\Enums\RefundMethodEnum::class,
            'reason' => 'string',
            'processed_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ]);
});

test('casts work correctly', function (): void {
    $saleReturn = SaleReturn::factory()->create()->refresh();

    expect($saleReturn->id)->toBeInt()
        ->and($saleReturn->total_amount)->toBeInt()
        ->and($saleReturn->return_date)->toBeInstanceOf(DateTimeInterface::class)
        ->and($saleReturn->created_at)->toBeInstanceOf(DateTimeInterface::class);
});

test('casts refund_method to RefundMethodEnum', function (): void {
    $saleReturn = SaleReturn::factory()->create([
        'refund_method' => App\Enums\RefundMethodEnum::CASH,
    ]);

    expect($saleReturn->refund_method)->toBeInstanceOf(App\Enums\RefundMethodEnum::class)
        ->and($saleReturn->refund_method)->toBe(App\Enums\RefundMethodEnum::CASH);
});

test('can set refund_method using enum value', function (): void {
    $saleReturn = SaleReturn::factory()->create([
        'refund_method' => 'store_credit',
    ]);

    expect($saleReturn->refund_method)->toBeInstanceOf(App\Enums\RefundMethodEnum::class)
        ->and($saleReturn->refund_method->value)->toBe('store_credit');
});

test('can access enum methods on refund_method', function (): void {
    $saleReturn = SaleReturn::factory()->create([
        'refund_method' => App\Enums\RefundMethodEnum::STORE_CREDIT,
    ]);

    expect($saleReturn->refund_method->label())->toBe('Store Credit')
        ->and($saleReturn->refund_method->color())->toBeString()
        ->and($saleReturn->refund_method->icon())->toBeString();
});
