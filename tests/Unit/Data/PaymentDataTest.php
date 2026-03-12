<?php

declare(strict_types=1);

use App\Data\Payment\PaymentData;
use App\Models\PaymentMethod;
use Illuminate\Validation\ValidationException;

describe(PaymentData::class, function (): void {
    describe('creation', function (): void {
        it('creates with required fields', function (): void {
            $data = new PaymentData(
                payment_method_id: 1,
                amount: 1000,
                payment_date: '2024-01-15',
                note: null,
            );

            expect($data)->toBeInstanceOf(PaymentData::class)
                ->and($data->payment_method_id)->toBe(1)
                ->and($data->amount)->toBe(1000)
                ->and($data->payment_date)->toBe('2024-01-15')
                ->and($data->note)->toBeNull();
        });

        it('creates with all optional fields', function (): void {
            $data = new PaymentData(
                payment_method_id: 1,
                amount: 500,
                payment_date: '2024-01-15',
                note: 'Payment note',
            );

            expect($data->note)->toBe('Payment note');
        });
    });

    describe('validation', function (): void {
        it('passes validation with valid data', function (): void {
            $method = PaymentMethod::factory()->create();

            $validated = PaymentData::validate([
                'payment_method_id' => $method->id,
                'amount' => 100,
                'payment_date' => '2024-01-15',
            ]);

            expect($validated['amount'])->toBe(100);
        });

        it('fails validation when payment_method_id is missing', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => PaymentData::validate([
                'amount' => 100,
                'payment_date' => '2024-01-15',
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when amount is missing', function (): void {
            $method = PaymentMethod::factory()->create();

            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => PaymentData::validate([
                'payment_method_id' => $method->id,
                'payment_date' => '2024-01-15',
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation with amount less than 1', function (): void {
            $method = PaymentMethod::factory()->create();

            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => PaymentData::validate([
                'payment_method_id' => $method->id,
                'amount' => 0,
                'payment_date' => '2024-01-15',
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when payment_date is missing', function (): void {
            $method = PaymentMethod::factory()->create();

            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => PaymentData::validate([
                'payment_method_id' => $method->id,
                'amount' => 100,
            ]))->toThrow(ValidationException::class);
        });
    });
});
