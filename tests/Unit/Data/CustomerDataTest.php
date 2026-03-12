<?php

declare(strict_types=1);

use App\Data\Customer\CustomerData;
use App\Models\Customer;
use Illuminate\Validation\ValidationException;

describe(CustomerData::class, function (): void {
    describe('creation', function (): void {
        it('creates with required fields', function (): void {
            $data = new CustomerData(
                name: 'John Doe',
                email: null,
                phone: null,
                address: null,
                city: null,
                country: null,
                is_active: true,
            );

            expect($data)->toBeInstanceOf(CustomerData::class)
                ->and($data->name)->toBe('John Doe')
                ->and($data->email)->toBeNull()
                ->and($data->is_active)->toBeTrue();
        });

        it('creates with all optional fields', function (): void {
            $data = new CustomerData(
                name: 'John Doe',
                email: 'john@example.com',
                phone: '+1234567890',
                address: '123 Main St',
                city: 'New York',
                country: 'USA',
                is_active: true,
            );

            expect($data->email)->toBe('john@example.com')
                ->and($data->phone)->toBe('+1234567890')
                ->and($data->address)->toBe('123 Main St')
                ->and($data->city)->toBe('New York')
                ->and($data->country)->toBe('USA');
        });

        it('creates with is_active false', function (): void {
            $data = new CustomerData(
                name: 'Inactive Customer',
                email: null,
                phone: null,
                address: null,
                city: null,
                country: null,
                is_active: false,
            );

            expect($data->is_active)->toBeFalse();
        });
    });

    describe('fromModel', function (): void {
        it('creates data from model', function (): void {
            $customer = Customer::factory()->create([
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'is_active' => true,
            ]);

            $data = CustomerData::fromModel($customer);

            expect($data)->toBeInstanceOf(CustomerData::class)
                ->and($data->name)->toBe('Jane Doe')
                ->and($data->email)->toBe('jane@example.com');
        });
    });

    describe('validation', function (): void {
        it('passes validation with valid data', function (): void {
            $validated = CustomerData::validate([
                'name' => 'Valid Customer',
                'email' => 'test@example.com',
                'phone' => '+1234567890',
                'is_active' => true,
            ]);

            expect($validated['name'])->toBe('Valid Customer');
        });

        it('fails validation when name is too short', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => CustomerData::validate([
                'name' => 'AB',
                'is_active' => true,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation with invalid email', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => CustomerData::validate([
                'name' => 'Valid Name',
                'email' => 'not-an-email',
                'is_active' => true,
            ]))->toThrow(ValidationException::class);
        });

        it('passes validation with nullable optional fields', function (): void {
            $validated = CustomerData::validate([
                'name' => 'Test Customer',
            ]);

            expect($validated['name'])->toBe('Test Customer');
        });

        it('fails validation when name exceeds max length', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => CustomerData::validate([
                'name' => str_repeat('a', 81),
                'is_active' => true,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when email exceeds max length', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => CustomerData::validate([
                'name' => 'Valid Name',
                'email' => str_repeat('a', 256).'@example.com',
                'is_active' => true,
            ]))->toThrow(ValidationException::class);
        });
    });
});
