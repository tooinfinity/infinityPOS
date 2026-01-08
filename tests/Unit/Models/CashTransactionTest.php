<?php

declare(strict_types=1);

use App\Models\CashTransaction;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

test('to array', function (): void {
    $cashTransaction = CashTransaction::factory()->create()->refresh();

    expect(array_keys($cashTransaction->toArray()))
        ->toBe([
            'id',
            'register_session_id',
            'transaction_type',
            'amount',
            'reference_type',
            'reference_id',
            'description',
            'created_by',
            'created_at',
        ]);
});

test('register session relationship returns belongs to', function (): void {
    $cashTransaction = new CashTransaction();

    expect($cashTransaction->registerSession())
        ->toBeInstanceOf(BelongsTo::class);
});

test('creator relationship returns belongs to', function (): void {
    $cashTransaction = new CashTransaction();

    expect($cashTransaction->creator())
        ->toBeInstanceOf(BelongsTo::class);
});

test('reference relationship returns morph to', function (): void {
    $cashTransaction = new CashTransaction();

    expect($cashTransaction->reference())
        ->toBeInstanceOf(MorphTo::class);
});

test('is inflow returns true when amount is positive', function (): void {
    $cashTransaction = CashTransaction::factory()->make(['amount' => 1000]);

    expect($cashTransaction->isInflow())->toBeTrue();
});

test('is inflow returns false when amount is zero', function (): void {
    $cashTransaction = CashTransaction::factory()->make(['amount' => 0]);

    expect($cashTransaction->isInflow())->toBeFalse();
});

test('is inflow returns false when amount is negative', function (): void {
    $cashTransaction = CashTransaction::factory()->make(['amount' => -1000]);

    expect($cashTransaction->isInflow())->toBeFalse();
});

test('is outflow returns true when amount is negative', function (): void {
    $cashTransaction = CashTransaction::factory()->make(['amount' => -1000]);

    expect($cashTransaction->isOutflow())->toBeTrue();
});

test('is outflow returns false when amount is zero', function (): void {
    $cashTransaction = CashTransaction::factory()->make(['amount' => 0]);

    expect($cashTransaction->isOutflow())->toBeFalse();
});

test('is outflow returns false when amount is positive', function (): void {
    $cashTransaction = CashTransaction::factory()->make(['amount' => 1000]);

    expect($cashTransaction->isOutflow())->toBeFalse();
});

test('casts returns correct array', function (): void {
    $cashTransaction = new CashTransaction();

    expect($cashTransaction->casts())
        ->toBe([
            'id' => 'integer',
            'register_session_id' => 'integer',
            'transaction_type' => App\Enums\CashTransactionTypeEnum::class,
            'amount' => 'integer',
            'reference_type' => 'string',
            'reference_id' => 'integer',
            'description' => 'string',
            'created_by' => 'integer',
            'created_at' => 'datetime',
        ]);
});

test('casts work correctly', function (): void {
    $cashTransaction = CashTransaction::factory()->create()->refresh();

    expect($cashTransaction->id)->toBeInt()
        ->and($cashTransaction->register_session_id)->toBeInt()
        ->and($cashTransaction->amount)->toBeInt()
        ->and($cashTransaction->created_by)->toBeInt()
        ->and($cashTransaction->created_at)->toBeInstanceOf(DateTimeInterface::class);
});

test('casts transaction_type to CashTransactionTypeEnum', function (): void {
    $cashTransaction = CashTransaction::factory()->create([
        'transaction_type' => App\Enums\CashTransactionTypeEnum::SALE,
    ]);

    expect($cashTransaction->transaction_type)->toBeInstanceOf(App\Enums\CashTransactionTypeEnum::class)
        ->and($cashTransaction->transaction_type)->toBe(App\Enums\CashTransactionTypeEnum::SALE);
});

test('can set transaction_type using enum value', function (): void {
    $cashTransaction = CashTransaction::factory()->create([
        'transaction_type' => 'expense',
    ]);

    expect($cashTransaction->transaction_type)->toBeInstanceOf(App\Enums\CashTransactionTypeEnum::class)
        ->and($cashTransaction->transaction_type->value)->toBe('expense');
});

test('can access enum methods on transaction_type', function (): void {
    $cashTransaction = CashTransaction::factory()->create([
        'transaction_type' => App\Enums\CashTransactionTypeEnum::SALE,
    ]);

    expect($cashTransaction->transaction_type->label())->toBe('Sale')
        ->and($cashTransaction->transaction_type->color())->toBeString()
        ->and($cashTransaction->transaction_type->icon())->toBeString()
        ->and($cashTransaction->transaction_type->isInflow())->toBeTrue();
});
