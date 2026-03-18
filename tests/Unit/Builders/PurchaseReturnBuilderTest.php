<?php

declare(strict_types=1);

use App\Models\PurchaseReturn;
use Illuminate\Pagination\LengthAwarePaginator;

describe('PurchaseReturnBuilder', function (): void {
    describe('search', function (): void {
        it('exists as a method', function (): void {
            $builder = PurchaseReturn::query();

            expect(method_exists($builder, 'search'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = PurchaseReturn::query();

            $result = $builder->search('test');

            expect($result)->toBe($builder);
        });
    });

    describe('status', function (): void {
        it('exists as a method', function (): void {
            $builder = PurchaseReturn::query();

            expect(method_exists($builder, 'status'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = PurchaseReturn::query();

            $result = $builder->status('test');

            expect($result)->toBe($builder);
        });
    });

    describe('paymentStatus', function (): void {
        it('exists as a method', function (): void {
            $builder = PurchaseReturn::query();

            expect(method_exists($builder, 'paymentStatus'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = PurchaseReturn::query();

            $result = $builder->paymentStatus('test');

            expect($result)->toBe($builder);
        });
    });

    describe('pending', function (): void {
        it('exists as a method', function (): void {
            $builder = PurchaseReturn::query();

            expect(method_exists($builder, 'pending'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = PurchaseReturn::query();

            $result = $builder->pending();

            expect($result)->toBe($builder);
        });
    });

    describe('completed', function (): void {
        it('exists as a method', function (): void {
            $builder = PurchaseReturn::query();

            expect(method_exists($builder, 'completed'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = PurchaseReturn::query();

            $result = $builder->completed();

            expect($result)->toBe($builder);
        });
    });

    describe('unpaid', function (): void {
        it('exists as a method', function (): void {
            $builder = PurchaseReturn::query();

            expect(method_exists($builder, 'unpaid'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = PurchaseReturn::query();

            $result = $builder->unpaid();

            expect($result)->toBe($builder);
        });
    });

    describe('partiallyPaid', function (): void {
        it('exists as a method', function (): void {
            $builder = PurchaseReturn::query();

            expect(method_exists($builder, 'partiallyPaid'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = PurchaseReturn::query();

            $result = $builder->partiallyPaid();

            expect($result)->toBe($builder);
        });
    });

    describe('paid', function (): void {
        it('exists as a method', function (): void {
            $builder = PurchaseReturn::query();

            expect(method_exists($builder, 'paid'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = PurchaseReturn::query();

            $result = $builder->paid();

            expect($result)->toBe($builder);
        });
    });

    describe('applyFilters', function (): void {
        it('exists as a method', function (): void {
            $builder = PurchaseReturn::query();

            expect(method_exists($builder, 'applyFilters'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = PurchaseReturn::query();

            $result = $builder->applyFilters([]);

            expect($result)->toBe($builder);
        });
    });

    describe('paginateWithFilters', function (): void {
        it('exists as a method', function (): void {
            $builder = PurchaseReturn::query();

            expect(method_exists($builder, 'paginateWithFilters'))->toBeTrue();
        });

        it('returns LengthAwarePaginator', function (): void {
            PurchaseReturn::factory()->count(3)->create();

            $result = PurchaseReturn::query()->paginateWithFilters([], 10);

            expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
        });
    });
});
