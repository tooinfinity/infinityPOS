<?php

declare(strict_types=1);

use App\Data\Expense\ExpenseData;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Validation\ValidationException;

describe(ExpenseData::class, function (): void {
    describe('creation', function (): void {
        it('creates with required fields', function (): void {
            $data = new ExpenseData(
                expense_category_id: 1,
                amount: 1000,
                expense_date: Illuminate\Support\Facades\Date::parse('2024-01-15'),
                description: null,
            );

            expect($data)->toBeInstanceOf(ExpenseData::class)
                ->and($data->expense_category_id)->toBe(1)
                ->and($data->amount)->toBe(1000)
                ->and($data->expense_date)->toBeInstanceOf(Carbon\CarbonInterface::class)
                ->and($data->description)->toBeNull();
        });

        it('creates with all optional fields', function (): void {
            $data = new ExpenseData(
                expense_category_id: 1,
                amount: 500,
                expense_date: Illuminate\Support\Facades\Date::parse('2024-01-15'),
                description: 'Monthly utilities payment',
            );

            expect($data->description)->toBe('Monthly utilities payment');
        });
    });

    describe('fromModel', function (): void {
        it('creates data from model', function (): void {
            $category = ExpenseCategory::factory()->create();
            $expense = Expense::factory()->create([
                'expense_category_id' => $category->id,
                'amount' => 250,
                'expense_date' => '2024-01-20',
                'description' => 'Office supplies',
            ]);

            $data = ExpenseData::fromModel($expense);

            expect($data)->toBeInstanceOf(ExpenseData::class)
                ->and($data->expense_category_id)->toBe($category->id)
                ->and($data->amount)->toBe(250);
        });
    });

    describe('validation', function (): void {
        it('passes validation with valid data', function (): void {
            $category = ExpenseCategory::factory()->create();

            $validated = ExpenseData::validate([
                'expense_category_id' => $category->id,
                'amount' => 100,
                'expense_date' => '2024-01-15',
            ]);

            expect($validated['amount'])->toBe(100);
        });

        it('fails validation when expense_category_id is missing', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => ExpenseData::validate([
                'amount' => 100,
                'expense_date' => '2024-01-15',
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when amount is missing', function (): void {
            $category = ExpenseCategory::factory()->create();

            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => ExpenseData::validate([
                'expense_category_id' => $category->id,
                'expense_date' => '2024-01-15',
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation with amount less than 1', function (): void {
            $category = ExpenseCategory::factory()->create();

            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => ExpenseData::validate([
                'expense_category_id' => $category->id,
                'amount' => 0,
                'expense_date' => '2024-01-15',
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when expense_date is missing', function (): void {
            $category = ExpenseCategory::factory()->create();

            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => ExpenseData::validate([
                'expense_category_id' => $category->id,
                'amount' => 100,
            ]))->toThrow(ValidationException::class);
        });
    });
});
