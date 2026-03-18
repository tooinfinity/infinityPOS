<?php

declare(strict_types=1);

use App\Models\ExpenseCategory;
use Illuminate\Pagination\LengthAwarePaginator;

describe('ExpenseCategoryBuilder', function (): void {
    describe('search', function (): void {
        it('exists as a method', function (): void {
            $builder = ExpenseCategory::query();

            expect(method_exists($builder, 'search'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = ExpenseCategory::query();

            $result = $builder->search('test');

            expect($result)->toBe($builder);
        });
    });

    describe('applyFilters', function (): void {
        it('exists as a method', function (): void {
            $builder = ExpenseCategory::query();

            expect(method_exists($builder, 'applyFilters'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = ExpenseCategory::query();

            $result = $builder->applyFilters([]);

            expect($result)->toBe($builder);
        });
    });

    describe('paginateWithFilters', function (): void {
        it('exists as a method', function (): void {
            $builder = ExpenseCategory::query();

            expect(method_exists($builder, 'paginateWithFilters'))->toBeTrue();
        });

        it('returns LengthAwarePaginator', function (): void {
            ExpenseCategory::factory()->count(3)->create();

            $result = ExpenseCategory::query()->paginateWithFilters([], 10);

            expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
        });
    });
});
