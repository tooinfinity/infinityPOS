<?php

declare(strict_types=1);

use App\Data\Payment\VoidPaymentData;
use Illuminate\Validation\ValidationException;

describe(VoidPaymentData::class, function (): void {
    describe('creation', function (): void {
        it('creates with required fields', function (): void {
            $data = new VoidPaymentData(
                void_reason: 'Customer requested cancellation',
            );

            expect($data)->toBeInstanceOf(VoidPaymentData::class)
                ->and($data->void_reason)->toBe('Customer requested cancellation');
        });
    });

    describe('validation', function (): void {
        it('passes validation with valid data', function (): void {
            $validated = VoidPaymentData::validate([
                'void_reason' => 'Valid reason for voiding payment',
            ]);

            expect($validated['void_reason'])->toBe('Valid reason for voiding payment');
        });

        it('fails validation when void_reason is missing', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => VoidPaymentData::validate([]))
                ->toThrow(ValidationException::class);
        });

        it('fails validation when void_reason exceeds max length', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => VoidPaymentData::validate([
                'void_reason' => str_repeat('a', 501),
            ]))->toThrow(ValidationException::class);
        });
    });
});
