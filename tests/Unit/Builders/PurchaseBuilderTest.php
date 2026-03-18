<?php

declare(strict_types=1);

use App\Models\Purchase;
use Illuminate\Pagination\LengthAwarePaginator;

describe('PurchaseBuilder', function (): void {
    describe('search', function (): void {
        it('exists as a method', function (): void {
            $builder = Purchase::query();

            expect(method_exists($builder, 'search'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Purchase::query();

            $result = $builder->search('test');

            expect($result)->toBe($builder);
        });
    });

    describe('status', function (): void {
        it('exists as a method', function (): void {
            $builder = Purchase::query();

            expect(method_exists($builder, 'status'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Purchase::query();

            $result = $builder->status('pending');

            expect($result)->toBe($builder);
        });
    });

    describe('paymentStatus', function (): void {
        it('exists as a method', function (): void {
            $builder = Purchase::query();

            expect(method_exists($builder, 'paymentStatus'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Purchase::query();

            $result = $builder->paymentStatus('paid');

            expect($result)->toBe($builder);
        });
    });

    describe('pending', function (): void {
        it('exists as a method', function (): void {
            $builder = Purchase::query();

            expect(method_exists($builder, 'pending'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Purchase::query();

            $result = $builder->pending();

            expect($result)->toBe($builder);
        });
    });

    describe('ordered', function (): void {
        it('exists as a method', function (): void {
            $builder = Purchase::query();

            expect(method_exists($builder, 'ordered'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Purchase::query();

            $result = $builder->ordered();

            expect($result)->toBe($builder);
        });
    });

    describe('received', function (): void {
        it('exists as a method', function (): void {
            $builder = Purchase::query();

            expect(method_exists($builder, 'received'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Purchase::query();

            $result = $builder->received();

            expect($result)->toBe($builder);
        });
    });

    describe('cancelled', function (): void {
        it('exists as a method', function (): void {
            $builder = Purchase::query();

            expect(method_exists($builder, 'cancelled'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Purchase::query();

            $result = $builder->cancelled();

            expect($result)->toBe($builder);
        });
    });

    describe('unpaid', function (): void {
        it('exists as a method', function (): void {
            $builder = Purchase::query();

            expect(method_exists($builder, 'unpaid'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Purchase::query();

            $result = $builder->unpaid();

            expect($result)->toBe($builder);
        });
    });

    describe('partiallyPaid', function (): void {
        it('exists as a method', function (): void {
            $builder = Purchase::query();

            expect(method_exists($builder, 'partiallyPaid'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Purchase::query();

            $result = $builder->partiallyPaid();

            expect($result)->toBe($builder);
        });
    });

    describe('paid', function (): void {
        it('exists as a method', function (): void {
            $builder = Purchase::query();

            expect(method_exists($builder, 'paid'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Purchase::query();

            $result = $builder->paid();

            expect($result)->toBe($builder);
        });
    });

    describe('paginateWithFilters', function (): void {
        it('exists as a method', function (): void {
            $builder = Purchase::query();

            expect(method_exists($builder, 'paginateWithFilters'))->toBeTrue();
        });

        it('returns LengthAwarePaginator', function (): void {
            Purchase::factory()->count(3)->create();

            $result = Purchase::query()->paginateWithFilters([], 10);

            expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
        });
    });
});
