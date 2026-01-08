<?php

declare(strict_types=1);

use App\DTOs\ExpenseData;
use App\Enums\ExpenseCategoryEnum;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Exceptions\CannotCreateData;

it('creates expense DTO from array', function (): void {
    $data = ExpenseData::from([
        'store_id' => 1,
        'register_session_id' => 10,
        'expense_category' => 'utilities',
        'amount' => 5000,
        'description' => 'Monthly electricity bill',
        'expense_date' => '2024-01-15',
        'recorded_by' => 5,
    ]);

    expect($data->storeId)->toBe(1)
        ->and($data->registerSessionId)->toBe(10)
        ->and($data->expenseCategory)->toBeInstanceOf(ExpenseCategoryEnum::class)
        ->and($data->expenseCategory)->toBe(ExpenseCategoryEnum::UTILITIES)
        ->and($data->amount)->toBe(5000)
        ->and($data->description)->toBe('Monthly electricity bill')
        ->and($data->expenseDate)->toBeInstanceOf(CarbonImmutable::class)
        ->and($data->expenseDate->format('Y-m-d'))->toBe('2024-01-15')
        ->and($data->recordedBy)->toBe(5);
});

it('creates expense DTO without register session', function (): void {
    $data = ExpenseData::from([
        'store_id' => 1,
        'expense_category' => 'supplies',
        'amount' => 10000,
        'description' => 'Office supplies',
        'expense_date' => '2024-01-01',
        'recorded_by' => 3,
    ]);

    expect($data->registerSessionId)->toBeNull()
        ->and($data->expenseCategory)->toBe(ExpenseCategoryEnum::SUPPLIES)
        ->and($data->amount)->toBe(10000);
});

it('validates required fields', function (): void {
    ExpenseData::from([
        'store_id' => 1,
        'amount' => 100,
    ]);
})->throws(CannotCreateData::class);

it('validates amount is non-negative', function (): void {
    ExpenseData::validateAndCreate([
        'store_id' => 1,
        'expense_category' => 'utilities',
        'amount' => -500,
        'description' => 'Test',
        'expense_date' => '2024-01-01',
        'recorded_by' => 1,
    ]);
})->throws(ValidationException::class);

it('validates expense category with enum', function (): void {
    $data = ExpenseData::from([
        'store_id' => 1,
        'expense_category' => 'maintenance',
        'amount' => 2000,
        'description' => 'Equipment maintenance',
        'expense_date' => '2024-01-15',
        'recorded_by' => 1,
    ]);

    expect($data->expenseCategory)->toBeInstanceOf(ExpenseCategoryEnum::class)
        ->and($data->expenseCategory)->toBe(ExpenseCategoryEnum::MAINTENANCE);
});

it('accepts enum case values for expense category', function (): void {
    $data = ExpenseData::from([
        'store_id' => 1,
        'expense_category' => ExpenseCategoryEnum::OTHER->value,
        'amount' => 1500,
        'description' => 'Miscellaneous expense',
        'expense_date' => '2024-01-15',
        'recorded_by' => 1,
    ]);

    expect($data->expenseCategory)->toBe(ExpenseCategoryEnum::OTHER);
});

it('can use enum directly for expense category', function (): void {
    $data = ExpenseData::from([
        'store_id' => 1,
        'expense_category' => ExpenseCategoryEnum::SUPPLIES,
        'amount' => 3000,
        'description' => 'Office supplies',
        'expense_date' => '2024-01-15',
        'recorded_by' => 1,
    ]);

    expect($data->expenseCategory)->toBe(ExpenseCategoryEnum::SUPPLIES);
});

it('rejects invalid expense category', function (): void {
    ExpenseData::validateAndCreate([
        'store_id' => 1,
        'expense_category' => 'invalid-category',
        'amount' => 1000,
        'description' => 'Test',
        'expense_date' => '2024-01-15',
        'recorded_by' => 1,
    ]);
})->throws(ValidationException::class);
