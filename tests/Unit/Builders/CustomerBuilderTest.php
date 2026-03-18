<?php

declare(strict_types=1);

use App\Models\Customer;
use Illuminate\Pagination\LengthAwarePaginator;

describe('CustomerBuilder', function (): void {
    describe('search', function (): void {
        it('exists as a method', function (): void {
            $builder = Customer::query();

            expect(method_exists($builder, 'search'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Customer::query();

            $result = $builder->search('test');

            expect($result)->toBe($builder);
        });
    });

    describe('applyFilters', function (): void {
        it('exists as a method', function (): void {
            $builder = Customer::query();

            expect(method_exists($builder, 'applyFilters'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Customer::query();

            $result = $builder->applyFilters([]);

            expect($result)->toBe($builder);
        });
    });

    describe('paginateWithFilters', function (): void {
        it('exists as a method', function (): void {
            $builder = Customer::query();

            expect(method_exists($builder, 'paginateWithFilters'))->toBeTrue();
        });

        it('returns LengthAwarePaginator', function (): void {
            Customer::factory()->count(3)->create();

            $result = Customer::query()->paginateWithFilters([], 10);

            expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
        });
    });
});
