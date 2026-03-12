<?php

declare(strict_types=1);

use App\Data\Supplier\SupplierData;
use App\Models\Supplier;
use Illuminate\Validation\ValidationException;

describe(SupplierData::class, function (): void {
    describe('creation', function (): void {
        it('creates with required fields', function (): void {
            $data = new SupplierData(
                name: 'John Supplier',
                company_name: null,
                email: null,
                phone: null,
                address: null,
                city: null,
                country: null,
                is_active: true,
            );

            expect($data)->toBeInstanceOf(SupplierData::class)
                ->and($data->name)->toBe('John Supplier')
                ->and($data->company_name)->toBeNull()
                ->and($data->is_active)->toBeTrue();
        });

        it('creates with all optional fields', function (): void {
            $data = new SupplierData(
                name: 'John Supplier',
                company_name: 'Supplier Co',
                email: 'supplier@example.com',
                phone: '+1234567890',
                address: '123 Supply St',
                city: 'Chicago',
                country: 'USA',
                is_active: true,
            );

            expect($data->company_name)->toBe('Supplier Co')
                ->and($data->email)->toBe('supplier@example.com')
                ->and($data->phone)->toBe('+1234567890')
                ->and($data->address)->toBe('123 Supply St')
                ->and($data->city)->toBe('Chicago')
                ->and($data->country)->toBe('USA');
        });

        it('creates with is_active false', function (): void {
            $data = new SupplierData(
                name: 'Inactive Supplier',
                company_name: null,
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
            $supplier = Supplier::factory()->create([
                'name' => 'Jane Supplier',
                'company_name' => 'Supply Inc',
                'email' => 'jane@supply.com',
                'is_active' => true,
            ]);

            $data = SupplierData::fromModel($supplier);

            expect($data)->toBeInstanceOf(SupplierData::class)
                ->and($data->name)->toBe('Jane Supplier')
                ->and($data->company_name)->toBe('Supply Inc')
                ->and($data->email)->toBe('jane@supply.com');
        });
    });

    describe('validation', function (): void {
        it('passes validation with valid data', function (): void {
            $validated = SupplierData::validate([
                'name' => 'Valid Supplier',
                'company_name' => 'Company Name',
                'email' => 'test@example.com',
                'is_active' => true,
            ]);

            expect($validated['name'])->toBe('Valid Supplier');
        });

        it('fails validation when name is too short', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => SupplierData::validate([
                'name' => 'AB',
                'is_active' => true,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation with invalid email', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => SupplierData::validate([
                'name' => 'Valid Name',
                'email' => 'not-an-email',
                'is_active' => true,
            ]))->toThrow(ValidationException::class);
        });

        it('passes validation with nullable optional fields', function (): void {
            $validated = SupplierData::validate([
                'name' => 'Test Supplier',
            ]);

            expect($validated['name'])->toBe('Test Supplier');
        });

        it('fails validation when name exceeds max length', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => SupplierData::validate([
                'name' => str_repeat('a', 81),
                'is_active' => true,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when company_name exceeds max length', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => SupplierData::validate([
                'name' => 'Valid Name',
                'company_name' => str_repeat('a', 81),
                'is_active' => true,
            ]))->toThrow(ValidationException::class);
        });
    });
});
