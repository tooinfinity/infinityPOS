<?php

declare(strict_types=1);

use App\Data\ExpenseCategory\ExpenseCategoryData;
use App\Models\ExpenseCategory;
use Illuminate\Validation\ValidationException;

describe(ExpenseCategoryData::class, function (): void {
    describe('creation', function (): void {
        it('creates with required fields', function (): void {
            $data = new ExpenseCategoryData(
                name: 'Utilities',
                description: null,
                is_active: true,
            );

            expect($data)->toBeInstanceOf(ExpenseCategoryData::class)
                ->and($data->name)->toBe('Utilities')
                ->and($data->description)->toBeNull()
                ->and($data->is_active)->toBeTrue();
        });

        it('creates with all optional fields', function (): void {
            $data = new ExpenseCategoryData(
                name: 'Utilities',
                description: 'Electricity, water, gas',
                is_active: true,
            );

            expect($data->description)->toBe('Electricity, water, gas');
        });

        it('creates with is_active false', function (): void {
            $data = new ExpenseCategoryData(
                name: 'Inactive Category',
                description: null,
                is_active: false,
            );

            expect($data->is_active)->toBeFalse();
        });
    });

    describe('fromModel', function (): void {
        it('creates data from model', function (): void {
            $category = ExpenseCategory::factory()->create([
                'name' => 'Rent',
                'description' => 'Monthly rent',
                'is_active' => true,
            ]);

            $data = ExpenseCategoryData::fromModel($category);

            expect($data)->toBeInstanceOf(ExpenseCategoryData::class)
                ->and($data->name)->toBe('Rent')
                ->and($data->description)->toBe('Monthly rent');
        });
    });

    describe('validation', function (): void {
        it('passes validation with valid data', function (): void {
            $validated = ExpenseCategoryData::validate([
                'name' => 'Travel',
                'description' => 'Business travel expenses',
                'is_active' => true,
            ]);

            expect($validated['name'])->toBe('Travel');
        });

        it('fails validation when name is too short', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => ExpenseCategoryData::validate([
                'name' => 'AB',
                'is_active' => true,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when name exceeds max length', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => ExpenseCategoryData::validate([
                'name' => str_repeat('a', 81),
                'is_active' => true,
            ]))->toThrow(ValidationException::class);
        });

        it('passes validation with nullable description', function (): void {
            $validated = ExpenseCategoryData::validate([
                'name' => 'Test Category',
            ]);

            expect($validated['name'])->toBe('Test Category');
        });
    });
});
