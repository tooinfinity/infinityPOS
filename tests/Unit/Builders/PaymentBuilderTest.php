<?php

declare(strict_types=1);

use App\Models\Payment;
use Illuminate\Pagination\LengthAwarePaginator;

describe('PaymentBuilder', function (): void {
    describe('search', function (): void {
        it('exists as a method', function (): void {
            $builder = Payment::query();

            expect(method_exists($builder, 'search'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Payment::query();

            $result = $builder->search('test');

            expect($result)->toBe($builder);
        });
    });

    describe('status', function (): void {
        it('exists as a method', function (): void {
            $builder = Payment::query();

            expect(method_exists($builder, 'status'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Payment::query();

            $result = $builder->status('test');

            expect($result)->toBe($builder);
        });
    });

    describe('active', function (): void {
        it('exists as a method', function (): void {
            $builder = Payment::query();

            expect(method_exists($builder, 'active'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Payment::query();

            $result = $builder->active();

            expect($result)->toBe($builder);
        });
    });

    describe('voided', function (): void {
        it('exists as a method', function (): void {
            $builder = Payment::query();

            expect(method_exists($builder, 'voided'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Payment::query();

            $result = $builder->voided();

            expect($result)->toBe($builder);
        });
    });

    describe('recent', function (): void {
        it('exists as a method', function (): void {
            $builder = Payment::query();

            expect(method_exists($builder, 'recent'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Payment::query();

            $result = $builder->recent();

            expect($result)->toBe($builder);
        });
    });

    describe('today', function (): void {
        it('exists as a method', function (): void {
            $builder = Payment::query();

            expect(method_exists($builder, 'today'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Payment::query();

            $result = $builder->today();

            expect($result)->toBe($builder);
        });
    });

    describe('refunds', function (): void {
        it('exists as a method', function (): void {
            $builder = Payment::query();

            expect(method_exists($builder, 'refunds'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Payment::query();

            $result = $builder->refunds();

            expect($result)->toBe($builder);
        });
    });

    describe('activeForPayable', function (): void {
        it('exists as a method', function (): void {
            $builder = Payment::query();

            expect(method_exists($builder, 'activeForPayable'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Payment::query();

            $result = $builder->activeForPayable(App\Models\Sale::class, 1);

            expect($result)->toBe($builder);
        });
    });

    describe('applyFilters', function (): void {
        it('exists as a method', function (): void {
            $builder = Payment::query();

            expect(method_exists($builder, 'applyFilters'))->toBeTrue();
        });

        it('returns self for chaining', function (): void {
            $builder = Payment::query();

            $result = $builder->applyFilters([]);

            expect($result)->toBe($builder);
        });
    });

    describe('paginateWithFilters', function (): void {
        it('exists as a method', function (): void {
            $builder = Payment::query();

            expect(method_exists($builder, 'paginateWithFilters'))->toBeTrue();
        });

        it('returns LengthAwarePaginator', function (): void {
            Payment::factory()->count(3)->create();

            $result = Payment::query()->paginateWithFilters([], 10);

            expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
        });
    });
});
