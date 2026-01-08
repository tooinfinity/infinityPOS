<?php

declare(strict_types=1);

use App\Models\Expense;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

test('to array', function (): void {
    $expense = Expense::factory()->create()->refresh();

    expect(array_keys($expense->toArray()))
        ->toBe([
            'id',
            'store_id',
            'register_session_id',
            'expense_category',
            'amount',
            'description',
            'expense_date',
            'recorded_by',
            'created_at',
            'updated_at',
        ]);
});

test('store relationship returns belongs to', function (): void {
    $expense = new Expense();

    expect($expense->store())
        ->toBeInstanceOf(BelongsTo::class);
});

test('register session relationship returns belongs to', function (): void {
    $expense = new Expense();

    expect($expense->registerSession())
        ->toBeInstanceOf(BelongsTo::class);
});

test('recorder relationship returns belongs to', function (): void {
    $expense = new Expense();

    expect($expense->recorder())
        ->toBeInstanceOf(BelongsTo::class);
});

test('casts returns correct array', function (): void {
    $expense = new Expense();

    expect($expense->casts())
        ->toBe([
            'id' => 'integer',
            'store_id' => 'integer',
            'register_session_id' => 'integer',
            'expense_category' => App\Enums\ExpenseCategoryEnum::class,
            'amount' => 'integer',
            'description' => 'string',
            'expense_date' => 'date',
            'recorded_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ]);
});

test('casts work correctly', function (): void {
    $expense = Expense::factory()->create()->refresh();

    expect($expense->id)->toBeInt()
        ->and($expense->store_id)->toBeInt()
        ->and($expense->amount)->toBeInt()
        ->and($expense->expense_date)->toBeInstanceOf(DateTimeInterface::class)
        ->and($expense->created_at)->toBeInstanceOf(DateTimeInterface::class);
});

test('casts expense_category to ExpenseCategoryEnum', function (): void {
    $expense = Expense::factory()->create([
        'expense_category' => App\Enums\ExpenseCategoryEnum::UTILITIES,
    ]);

    expect($expense->expense_category)->toBeInstanceOf(App\Enums\ExpenseCategoryEnum::class)
        ->and($expense->expense_category)->toBe(App\Enums\ExpenseCategoryEnum::UTILITIES);
});

test('can set expense_category using enum value', function (): void {
    $expense = Expense::factory()->create([
        'expense_category' => 'supplies',
    ]);

    expect($expense->expense_category)->toBeInstanceOf(App\Enums\ExpenseCategoryEnum::class)
        ->and($expense->expense_category->value)->toBe('supplies');
});

test('can access enum methods on expense_category', function (): void {
    $expense = Expense::factory()->create([
        'expense_category' => App\Enums\ExpenseCategoryEnum::MAINTENANCE,
    ]);

    expect($expense->expense_category->label())->toBe('Maintenance')
        ->and($expense->expense_category->color())->toBeString()
        ->and($expense->expense_category->icon())->toBeString();
});
