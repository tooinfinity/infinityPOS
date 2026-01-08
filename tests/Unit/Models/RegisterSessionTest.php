<?php

declare(strict_types=1);

use App\Models\RegisterSession;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

test('to array', function (): void {
    $registerSession = RegisterSession::factory()->create()->refresh();

    expect(array_keys($registerSession->toArray()))
        ->toBe([
            'id',
            'cash_register_id',
            'opened_by',
            'closed_by',
            'opening_time',
            'closing_time',
            'opening_balance',
            'expected_cash',
            'actual_cash',
            'difference',
            'notes',
            'status',
            'created_at',
            'updated_at',
        ]);
});

test('cash register relationship returns belongs to', function (): void {
    $registerSession = new RegisterSession();

    expect($registerSession->cashRegister())
        ->toBeInstanceOf(BelongsTo::class);
});

test('opened by relationship returns belongs to', function (): void {
    $registerSession = new RegisterSession();

    expect($registerSession->openedBy())
        ->toBeInstanceOf(BelongsTo::class);
});

test('closed by relationship returns belongs to', function (): void {
    $registerSession = new RegisterSession();

    expect($registerSession->closedBy())
        ->toBeInstanceOf(BelongsTo::class);
});

test('sales relationship returns has many', function (): void {
    $registerSession = new RegisterSession();

    expect($registerSession->sales())
        ->toBeInstanceOf(HasMany::class);
});

test('cash transactions relationship returns has many', function (): void {
    $registerSession = new RegisterSession();

    expect($registerSession->cashTransactions())
        ->toBeInstanceOf(HasMany::class);
});

test('expenses relationship returns has many', function (): void {
    $registerSession = new RegisterSession();

    expect($registerSession->expenses())
        ->toBeInstanceOf(HasMany::class);
});

test('get cash variance returns difference when set', function (): void {
    $registerSession = RegisterSession::factory()->make(['difference' => 150]);

    expect($registerSession->getCashVariance())->toBe(150);
});

test('get cash variance returns zero when difference is null', function (): void {
    $registerSession = RegisterSession::factory()->make(['difference' => null]);

    expect($registerSession->getCashVariance())->toBe(0);
});

test('get cash variance returns negative value when difference is negative', function (): void {
    $registerSession = RegisterSession::factory()->make(['difference' => -250]);

    expect($registerSession->getCashVariance())->toBe(-250);
});

test('casts returns correct array', function (): void {
    $registerSession = new RegisterSession();

    expect($registerSession->casts())
        ->toBe([
            'id' => 'integer',
            'cash_register_id' => 'integer',
            'opened_by' => 'integer',
            'closed_by' => 'integer',
            'opening_time' => 'datetime',
            'closing_time' => 'datetime',
            'opening_balance' => 'integer',
            'expected_cash' => 'integer',
            'actual_cash' => 'integer',
            'difference' => 'integer',
            'notes' => 'string',
            'status' => App\Enums\RegisterSessionStatusEnum::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ]);
});

test('casts work correctly', function (): void {
    $registerSession = RegisterSession::factory()->create()->refresh();

    expect($registerSession->id)->toBeInt()
        ->and($registerSession->opening_balance)->toBeInt()
        ->and($registerSession->opening_time)->toBeInstanceOf(DateTimeInterface::class)
        ->and($registerSession->created_at)->toBeInstanceOf(DateTimeInterface::class);
});

test('casts status to RegisterSessionStatusEnum', function (): void {
    $registerSession = RegisterSession::factory()->create([
        'status' => App\Enums\RegisterSessionStatusEnum::OPEN,
    ]);

    expect($registerSession->status)->toBeInstanceOf(App\Enums\RegisterSessionStatusEnum::class)
        ->and($registerSession->status)->toBe(App\Enums\RegisterSessionStatusEnum::OPEN);
});

test('can set status using enum value', function (): void {
    $registerSession = RegisterSession::factory()->create([
        'status' => 'closed',
    ]);

    expect($registerSession->status)->toBeInstanceOf(App\Enums\RegisterSessionStatusEnum::class)
        ->and($registerSession->status->value)->toBe('closed');
});

test('can access enum methods on status', function (): void {
    $registerSession = RegisterSession::factory()->create([
        'status' => App\Enums\RegisterSessionStatusEnum::OPEN,
    ]);

    expect($registerSession->status->label())->toBe('Open')
        ->and($registerSession->status->color())->toBeString()
        ->and($registerSession->status->icon())->toBeString()
        ->and($registerSession->status->isOpen())->toBeTrue()
        ->and($registerSession->status->isClosed())->toBeFalse();
});
