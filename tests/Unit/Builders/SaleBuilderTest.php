<?php

declare(strict_types=1);

use App\Models\Sale;
use Illuminate\Pagination\LengthAwarePaginator;

describe('SaleBuilder', function (): void {
    describe('search', function (): void {
        it('exists as a method', function (): void {
            $builder = Sale::query();

            expect(method_exists($builder, 'search'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Sale::query();

            $result = $builder->search('test');

            expect($result)->toBe($builder);
        });
    });

    describe('status', function (): void {
        it('exists as a method', function (): void {
            $builder = Sale::query();

            expect(method_exists($builder, 'status'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Sale::query();

            $result = $builder->status('pending');

            expect($result)->toBe($builder);
        });
    });

    describe('paymentStatus', function (): void {
        it('exists as a method', function (): void {
            $builder = Sale::query();

            expect(method_exists($builder, 'paymentStatus'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Sale::query();

            $result = $builder->paymentStatus('paid');

            expect($result)->toBe($builder);
        });
    });

    describe('pending', function (): void {
        it('exists as a method', function (): void {
            $builder = Sale::query();

            expect(method_exists($builder, 'pending'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Sale::query();

            $result = $builder->pending();

            expect($result)->toBe($builder);
        });
    });

    describe('completed', function (): void {
        it('exists as a method', function (): void {
            $builder = Sale::query();

            expect(method_exists($builder, 'completed'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Sale::query();

            $result = $builder->completed();

            expect($result)->toBe($builder);
        });
    });

    describe('cancelled', function (): void {
        it('exists as a method', function (): void {
            $builder = Sale::query();

            expect(method_exists($builder, 'cancelled'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Sale::query();

            $result = $builder->cancelled();

            expect($result)->toBe($builder);
        });
    });

    describe('unpaid', function (): void {
        it('exists as a method', function (): void {
            $builder = Sale::query();

            expect(method_exists($builder, 'unpaid'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Sale::query();

            $result = $builder->unpaid();

            expect($result)->toBe($builder);
        });
    });

    describe('partiallyPaid', function (): void {
        it('exists as a method', function (): void {
            $builder = Sale::query();

            expect(method_exists($builder, 'partiallyPaid'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Sale::query();

            $result = $builder->partiallyPaid();

            expect($result)->toBe($builder);
        });
    });

    describe('paid', function (): void {
        it('exists as a method', function (): void {
            $builder = Sale::query();

            expect(method_exists($builder, 'paid'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Sale::query();

            $result = $builder->paid();

            expect($result)->toBe($builder);
        });
    });

    describe('today', function (): void {
        it('exists as a method', function (): void {
            $builder = Sale::query();

            expect(method_exists($builder, 'today'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Sale::query();

            $result = $builder->today();

            expect($result)->toBe($builder);
        });
    });

    describe('paginateWithFilters', function (): void {
        it('exists as a method', function (): void {
            $builder = Sale::query();

            expect(method_exists($builder, 'paginateWithFilters'))->toBeTrue();
        });

        it('returns LengthAwarePaginator', function (): void {
            Sale::factory()->count(3)->create();

            $result = Sale::query()->paginateWithFilters([], 10);

            expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
        });

        it('eager loads customer relationship', function (): void {
            Sale::factory()->create();

            $result = Sale::query()->paginateWithFilters([], 10);

            expect($result->first()->relationLoaded('customer'))->toBeTrue();
        });

        it('eager loads warehouse relationship', function (): void {
            Sale::factory()->create();

            $result = Sale::query()->paginateWithFilters([], 10);

            expect($result->first()->relationLoaded('warehouse'))->toBeTrue();
        });

        it('eager loads user relationship', function (): void {
            Sale::factory()->create();

            $result = Sale::query()->paginateWithFilters([], 10);

            expect($result->first()->relationLoaded('user'))->toBeTrue();
        });
    });
});
