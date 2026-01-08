<?php

declare(strict_types=1);

use App\Collections\SaleCollection;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

test('to array', function (): void {
    $sale = Sale::factory()->create()->refresh();

    expect(array_keys($sale->toArray()))
        ->toBe([
            'id',
            'store_id',
            'customer_id',
            'register_session_id',
            'invoice_number',
            'sale_date',
            'subtotal',
            'discount_amount',
            'total_amount',
            'payment_method',
            'amount_paid',
            'change_given',
            'status',
            'notes',
            'cashier_id',
            'created_at',
            'updated_at',
        ]);
});

test('new collection returns sale collection', function (): void {
    $sale = new Sale();

    expect($sale->newCollection([]))
        ->toBeInstanceOf(SaleCollection::class);
});

test('store relationship returns belongs to', function (): void {
    $sale = new Sale();

    expect($sale->store())
        ->toBeInstanceOf(BelongsTo::class);
});

test('customer relationship returns belongs to', function (): void {
    $sale = new Sale();

    expect($sale->customer())
        ->toBeInstanceOf(BelongsTo::class);
});

test('register session relationship returns belongs to', function (): void {
    $sale = new Sale();

    expect($sale->registerSession())
        ->toBeInstanceOf(BelongsTo::class);
});

test('cashier relationship returns belongs to', function (): void {
    $sale = new Sale();

    expect($sale->cashier())
        ->toBeInstanceOf(BelongsTo::class);
});

test('items relationship returns has many', function (): void {
    $sale = new Sale();

    expect($sale->items())
        ->toBeInstanceOf(HasMany::class);
});

test('payments relationship returns has many', function (): void {
    $sale = new Sale();

    expect($sale->payments())
        ->toBeInstanceOf(HasMany::class);
});

test('returns relationship returns has many', function (): void {
    $sale = new Sale();

    expect($sale->returns())
        ->toBeInstanceOf(HasMany::class);
});

test('get total profit sums profit from items', function (): void {
    $sale = Sale::factory()->create();
    SaleItem::factory()->create(['sale_id' => $sale->id, 'profit' => 1000]);
    SaleItem::factory()->create(['sale_id' => $sale->id, 'profit' => 1500]);
    $sale->refresh();

    expect($sale->getTotalProfit())->toBe(2500);
});

test('get total profit returns zero when no items', function (): void {
    $sale = Sale::factory()->create();

    expect($sale->getTotalProfit())->toBe(0);
});

test('is completed returns true when status is completed', function (): void {
    $sale = Sale::factory()->make(['status' => 'completed']);

    expect($sale->isCompleted())->toBeTrue();
});

test('is completed returns false when status is not completed', function (): void {
    $sale = Sale::factory()->make(['status' => 'pending']);

    expect($sale->isCompleted())->toBeFalse();
});

test('casts returns correct array', function (): void {
    $sale = new Sale();

    expect($sale->casts())
        ->toBe([
            'id' => 'integer',
            'store_id' => 'integer',
            'customer_id' => 'integer',
            'register_session_id' => 'integer',
            'invoice_number' => 'string',
            'sale_date' => 'datetime',
            'subtotal' => 'integer',
            'discount_amount' => 'integer',
            'total_amount' => 'integer',
            'payment_method' => App\Enums\PaymentMethodEnum::class,
            'amount_paid' => 'integer',
            'change_given' => 'integer',
            'status' => App\Enums\SaleStatusEnum::class,
            'notes' => 'string',
            'cashier_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ]);
});

test('casts work correctly', function (): void {
    $sale = Sale::factory()->create()->refresh();

    expect($sale->id)->toBeInt()
        ->and($sale->total_amount)->toBeInt()
        ->and($sale->sale_date)->toBeInstanceOf(DateTimeInterface::class)
        ->and($sale->created_at)->toBeInstanceOf(DateTimeInterface::class);
});

test('casts payment_method to PaymentMethodEnum', function (): void {
    $sale = Sale::factory()->create([
        'payment_method' => App\Enums\PaymentMethodEnum::CASH,
    ]);

    expect($sale->payment_method)->toBeInstanceOf(App\Enums\PaymentMethodEnum::class)
        ->and($sale->payment_method)->toBe(App\Enums\PaymentMethodEnum::CASH);
});

test('can set payment_method using enum value', function (): void {
    $sale = Sale::factory()->create([
        'payment_method' => 'card',
    ]);

    expect($sale->payment_method)->toBeInstanceOf(App\Enums\PaymentMethodEnum::class)
        ->and($sale->payment_method->value)->toBe('card');
});

test('can access enum methods on payment_method', function (): void {
    $sale = Sale::factory()->create([
        'payment_method' => App\Enums\PaymentMethodEnum::CASH,
    ]);

    expect($sale->payment_method->label())->toBe('Cash')
        ->and($sale->payment_method->color())->toBeString()
        ->and($sale->payment_method->icon())->toBeString()
        ->and($sale->payment_method->isCash())->toBeTrue();
});

test('casts status to SaleStatusEnum', function (): void {
    $sale = Sale::factory()->create([
        'status' => App\Enums\SaleStatusEnum::COMPLETED,
    ]);

    expect($sale->status)->toBeInstanceOf(App\Enums\SaleStatusEnum::class)
        ->and($sale->status)->toBe(App\Enums\SaleStatusEnum::COMPLETED);
});

test('can set status using enum value', function (): void {
    $sale = Sale::factory()->create([
        'status' => 'pending',
    ]);

    expect($sale->status)->toBeInstanceOf(App\Enums\SaleStatusEnum::class)
        ->and($sale->status->value)->toBe('pending');
});

test('can access enum methods on status', function (): void {
    $sale = Sale::factory()->create([
        'status' => App\Enums\SaleStatusEnum::COMPLETED,
    ]);

    expect($sale->status->label())->toBe('Completed')
        ->and($sale->status->color())->toBeString()
        ->and($sale->status->icon())->toBeString()
        ->and($sale->status->isCompleted())->toBeTrue()
        ->and($sale->status->canBeReturned())->toBeTrue();
});
