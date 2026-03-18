<?php

declare(strict_types=1);

use App\Models\StockMovement;
use Illuminate\Pagination\LengthAwarePaginator;

describe('StockMovementBuilder', function (): void {
    describe('search', function (): void {
        it('exists as a method', function (): void {
            $builder = StockMovement::query();

            expect(method_exists($builder, 'search'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = StockMovement::query();

            $result = $builder->search('test');

            expect($result)->toBe($builder);
        });
    });

    describe('in', function (): void {
        it('exists as a method', function (): void {
            $builder = StockMovement::query();

            expect(method_exists($builder, 'in'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = StockMovement::query();

            $result = $builder->in();

            expect($result)->toBe($builder);
        });
    });

    describe('out', function (): void {
        it('exists as a method', function (): void {
            $builder = StockMovement::query();

            expect(method_exists($builder, 'out'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = StockMovement::query();

            $result = $builder->out();

            expect($result)->toBe($builder);
        });
    });

    describe('transfer', function (): void {
        it('exists as a method', function (): void {
            $builder = StockMovement::query();

            expect(method_exists($builder, 'transfer'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = StockMovement::query();

            $result = $builder->transfer();

            expect($result)->toBe($builder);
        });
    });

    describe('adjustment', function (): void {
        it('exists as a method', function (): void {
            $builder = StockMovement::query();

            expect(method_exists($builder, 'adjustment'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = StockMovement::query();

            $result = $builder->adjustment();

            expect($result)->toBe($builder);
        });
    });

    describe('type', function (): void {
        it('exists as a method', function (): void {
            $builder = StockMovement::query();

            expect(method_exists($builder, 'type'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = StockMovement::query();

            $result = $builder->type('test');

            expect($result)->toBe($builder);
        });
    });

    describe('recent', function (): void {
        it('exists as a method', function (): void {
            $builder = StockMovement::query();

            expect(method_exists($builder, 'recent'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = StockMovement::query();

            $result = $builder->recent();

            expect($result)->toBe($builder);
        });
    });

    describe('applyFilters', function (): void {
        it('exists as a method', function (): void {
            $builder = StockMovement::query();

            expect(method_exists($builder, 'applyFilters'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = StockMovement::query();

            $result = $builder->applyFilters([]);

            expect($result)->toBe($builder);
        });
    });

    describe('paginateWithFilters', function (): void {
        it('exists as a method', function (): void {
            $builder = StockMovement::query();

            expect(method_exists($builder, 'paginateWithFilters'))->toBeTrue();
        });

        it('returns LengthAwarePaginator', function (): void {
            StockMovement::factory()->count(3)->create();

            $result = StockMovement::query()->paginateWithFilters([], 10);

            expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
        });
    });
});
