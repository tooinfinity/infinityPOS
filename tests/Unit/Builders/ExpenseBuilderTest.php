<?php

declare(strict_types=1);

use App\Models\Expense;
use Illuminate\Pagination\LengthAwarePaginator;

describe('ExpenseBuilder', function (): void {
    describe('search', function (): void {
        it('exists as a method', function (): void {
            $builder = Expense::query();

            expect(method_exists($builder, 'search'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Expense::query();

            $result = $builder->search('test');

            expect($result)->toBe($builder);
        });
    });

    describe('recent', function (): void {
        it('exists as a method', function (): void {
            $builder = Expense::query();

            expect(method_exists($builder, 'recent'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Expense::query();

            $result = $builder->recent();

            expect($result)->toBe($builder);
        });
    });

    describe('today', function (): void {
        it('exists as a method', function (): void {
            $builder = Expense::query();

            expect(method_exists($builder, 'today'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Expense::query();

            $result = $builder->today();

            expect($result)->toBe($builder);
        });
    });

    describe('applyFilters', function (): void {
        it('exists as a method', function (): void {
            $builder = Expense::query();

            expect(method_exists($builder, 'applyFilters'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Expense::query();

            $result = $builder->applyFilters([]);

            expect($result)->toBe($builder);
        });
    });

    describe('paginateWithFilters', function (): void {
        it('exists as a method', function (): void {
            $builder = Expense::query();

            expect(method_exists($builder, 'paginateWithFilters'))->toBeTrue();
        });

        it('returns LengthAwarePaginator', function (): void {
            Expense::factory()->count(3)->create();

            $result = Expense::query()->paginateWithFilters([], 10);

            expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
        });
    });
});
