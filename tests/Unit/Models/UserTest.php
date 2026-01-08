<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

test('to array', function (): void {
    $user = User::factory()->create()->refresh();

    expect(array_keys($user->toArray()))
        ->toBe([
            'id',
            'name',
            'email',
            'email_verified_at',
            'created_at',
            'updated_at',
            'store_id',
            'is_active',
        ]);
});

test('store relationship returns belongs to', function (): void {
    $user = new User();

    expect($user->store())
        ->toBeInstanceOf(BelongsTo::class);
});

test('created purchases relationship returns has many', function (): void {
    $user = new User();

    expect($user->createdPurchases())
        ->toBeInstanceOf(HasMany::class);
});

test('cashier sales relationship returns has many', function (): void {
    $user = new User();

    expect($user->cashierSales())
        ->toBeInstanceOf(HasMany::class);
});

test('created invoices relationship returns has many', function (): void {
    $user = new User();

    expect($user->createdInvoices())
        ->toBeInstanceOf(HasMany::class);
});

test('recorded invoice payments relationship returns has many', function (): void {
    $user = new User();

    expect($user->recordedInvoicePayments())
        ->toBeInstanceOf(HasMany::class);
});

test('processed returns relationship returns has many', function (): void {
    $user = new User();

    expect($user->processedReturns())
        ->toBeInstanceOf(HasMany::class);
});

test('stock adjustments relationship returns has many', function (): void {
    $user = new User();

    expect($user->stockAdjustments())
        ->toBeInstanceOf(HasMany::class);
});

test('recorded expenses relationship returns has many', function (): void {
    $user = new User();

    expect($user->recordedExpenses())
        ->toBeInstanceOf(HasMany::class);
});

test('created cash transactions relationship returns has many', function (): void {
    $user = new User();

    expect($user->createdCashTransactions())
        ->toBeInstanceOf(HasMany::class);
});

test('opened register sessions relationship returns has many', function (): void {
    $user = new User();

    expect($user->openedRegisterSessions())
        ->toBeInstanceOf(HasMany::class);
});

test('closed register sessions relationship returns has many', function (): void {
    $user = new User();

    expect($user->closedRegisterSessions())
        ->toBeInstanceOf(HasMany::class);
});

test('casts returns correct array', function (): void {
    $user = new User();

    expect($user->casts())
        ->toBe([
            'id' => 'integer',
            'store_id' => 'integer',
            'name' => 'string',
            'email' => 'string',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'remember_token' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ]);
});

test('casts work correctly', function (): void {
    $user = User::factory()->create(['is_active' => true])->refresh();

    expect($user->id)->toBeInt()
        ->and($user->name)->toBeString()
        ->and($user->email)->toBeString()
        ->and($user->is_active)->toBeTrue()
        ->and($user->created_at)->toBeInstanceOf(DateTimeInterface::class);
});
