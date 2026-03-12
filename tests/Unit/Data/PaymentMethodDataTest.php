<?php

declare(strict_types=1);

use App\Data\Payment\PaymentMethodData;
use App\Models\PaymentMethod;
use Illuminate\Validation\ValidationException;

describe(PaymentMethodData::class, function (): void {
    describe('creation', function (): void {
        it('creates with required fields', function (): void {
            $data = new PaymentMethodData(
                name: 'Cash',
                code: 'CASH',
                is_active: true,
            );

            expect($data)->toBeInstanceOf(PaymentMethodData::class)
                ->and($data->name)->toBe('Cash')
                ->and($data->code)->toBe('CASH')
                ->and($data->is_active)->toBeTrue();
        });

        it('creates with is_active false', function (): void {
            $data = new PaymentMethodData(
                name: 'Credit Card',
                code: 'CC',
                is_active: false,
            );

            expect($data->is_active)->toBeFalse();
        });
    });

    describe('fromModel', function (): void {
        it('creates data from model', function (): void {
            $method = PaymentMethod::factory()->create([
                'name' => 'Bank Transfer',
                'code' => 'BANK',
                'is_active' => true,
            ]);

            $data = PaymentMethodData::fromModel($method);

            expect($data)->toBeInstanceOf(PaymentMethodData::class)
                ->and($data->name)->toBe('Bank Transfer')
                ->and($data->code)->toBe('BANK');
        });
    });

    describe('validation', function (): void {
        it('passes validation with valid data', function (): void {
            $validated = PaymentMethodData::validate([
                'name' => 'Valid Method',
                'code' => 'VM001',
                'is_active' => true,
            ]);

            expect($validated['name'])->toBe('Valid Method');
        });

        it('fails validation when name is missing', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => PaymentMethodData::validate([
                'code' => 'VM001',
                'is_active' => true,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when code is missing', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => PaymentMethodData::validate([
                'name' => 'Valid Method',
                'is_active' => true,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when name exceeds max length', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => PaymentMethodData::validate([
                'name' => str_repeat('a', 81),
                'code' => 'VM001',
                'is_active' => true,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when code exceeds max length', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => PaymentMethodData::validate([
                'name' => 'Valid Method',
                'code' => str_repeat('a', 21),
                'is_active' => true,
            ]))->toThrow(ValidationException::class);
        });
    });
});
