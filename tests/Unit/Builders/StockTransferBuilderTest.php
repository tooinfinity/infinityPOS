<?php

declare(strict_types=1);

use App\Models\StockTransfer;
use Illuminate\Pagination\LengthAwarePaginator;

describe('StockTransferBuilder', function (): void {
    describe('search', function (): void {
        it('exists as a method', function (): void {
            $builder = StockTransfer::query();

            expect(method_exists($builder, 'search'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = StockTransfer::query();

            $result = $builder->search('test');

            expect($result)->toBe($builder);
        });
    });

    describe('pending', function (): void {
        it('exists as a method', function (): void {
            $builder = StockTransfer::query();

            expect(method_exists($builder, 'pending'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = StockTransfer::query();

            $result = $builder->pending();

            expect($result)->toBe($builder);
        });
    });

    describe('completed', function (): void {
        it('exists as a method', function (): void {
            $builder = StockTransfer::query();

            expect(method_exists($builder, 'completed'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = StockTransfer::query();

            $result = $builder->completed();

            expect($result)->toBe($builder);
        });
    });

    describe('cancelled', function (): void {
        it('exists as a method', function (): void {
            $builder = StockTransfer::query();

            expect(method_exists($builder, 'cancelled'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = StockTransfer::query();

            $result = $builder->cancelled();

            expect($result)->toBe($builder);
        });
    });

    describe('status', function (): void {
        it('exists as a method', function (): void {
            $builder = StockTransfer::query();

            expect(method_exists($builder, 'status'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = StockTransfer::query();

            $result = $builder->status('test');

            expect($result)->toBe($builder);
        });
    });

    describe('applyFilters', function (): void {
        it('exists as a method', function (): void {
            $builder = StockTransfer::query();

            expect(method_exists($builder, 'applyFilters'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = StockTransfer::query();

            $result = $builder->applyFilters([]);

            expect($result)->toBe($builder);
        });
    });

    describe('paginateWithFilters', function (): void {
        it('exists as a method', function (): void {
            $builder = StockTransfer::query();

            expect(method_exists($builder, 'paginateWithFilters'))->toBeTrue();
        });

        it('returns LengthAwarePaginator', function (): void {
            StockTransfer::factory()->count(3)->create();

            $result = StockTransfer::query()->paginateWithFilters([], 10);

            expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
        });
    });
});
