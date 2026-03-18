<?php

declare(strict_types=1);

use App\Models\Batch;
use Illuminate\Pagination\LengthAwarePaginator;

describe('BatchBuilder', function (): void {
    describe('search', function (): void {
        it('exists as a method', function (): void {
            $builder = Batch::query();

            expect(method_exists($builder, 'search'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Batch::query();

            $result = $builder->search('test');

            expect($result)->toBe($builder);
        });
    });

    describe('inStock', function (): void {
        it('exists as a method', function (): void {
            $builder = Batch::query();

            expect(method_exists($builder, 'inStock'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Batch::query();

            $result = $builder->inStock();

            expect($result)->toBe($builder);
        });
    });

    describe('expired', function (): void {
        it('exists as a method', function (): void {
            $builder = Batch::query();

            expect(method_exists($builder, 'expired'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Batch::query();

            $result = $builder->expired();

            expect($result)->toBe($builder);
        });
    });

    describe('expiringSoon', function (): void {
        it('exists as a method', function (): void {
            $builder = Batch::query();

            expect(method_exists($builder, 'expiringSoon'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Batch::query();

            $result = $builder->expiringSoon();

            expect($result)->toBe($builder);
        });
    });

    describe('fifo', function (): void {
        it('exists as a method', function (): void {
            $builder = Batch::query();

            expect(method_exists($builder, 'fifo'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Batch::query();

            $result = $builder->fifo();

            expect($result)->toBe($builder);
        });
    });

    describe('fefo', function (): void {
        it('exists as a method', function (): void {
            $builder = Batch::query();

            expect(method_exists($builder, 'fefo'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Batch::query();

            $result = $builder->fefo();

            expect($result)->toBe($builder);
        });
    });

    describe('matching', function (): void {
        it('exists as a method', function (): void {
            $builder = Batch::query();

            expect(method_exists($builder, 'matching'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Batch::query();

            $result = $builder->matching(1, 1, 1000);

            expect($result)->toBe($builder);
        });
    });

    describe('applyFilters', function (): void {
        it('exists as a method', function (): void {
            $builder = Batch::query();

            expect(method_exists($builder, 'applyFilters'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Batch::query();

            $result = $builder->applyFilters([]);

            expect($result)->toBe($builder);
        });
    });

    describe('paginateWithFilters', function (): void {
        it('exists as a method', function (): void {
            $builder = Batch::query();

            expect(method_exists($builder, 'paginateWithFilters'))->toBeTrue();
        });

        it('returns LengthAwarePaginator', function (): void {
            Batch::factory()->count(3)->create();

            $result = Batch::query()->paginateWithFilters([], 10);

            expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
        });
    });
});
