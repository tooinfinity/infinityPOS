<?php

declare(strict_types=1);

use App\Data\Warehouse\WarehouseData;
use App\Models\Warehouse;
use Illuminate\Validation\ValidationException;

describe(WarehouseData::class, function (): void {
    describe('creation', function (): void {
        it('creates with required fields', function (): void {
            $data = new WarehouseData(
                name: 'Main Warehouse',
                code: 'WH001',
                email: null,
                phone: null,
                address: null,
                city: null,
                country: null,
                is_active: true,
            );

            expect($data)->toBeInstanceOf(WarehouseData::class)
                ->and($data->name)->toBe('Main Warehouse')
                ->and($data->code)->toBe('WH001')
                ->and($data->is_active)->toBeTrue();
        });

        it('creates with all optional fields', function (): void {
            $data = new WarehouseData(
                name: 'Main Warehouse',
                code: 'WH001',
                email: 'warehouse@example.com',
                phone: '+1234567890',
                address: '123 Main St',
                city: 'New York',
                country: 'USA',
                is_active: true,
            );

            expect($data->email)->toBe('warehouse@example.com')
                ->and($data->phone)->toBe('+1234567890')
                ->and($data->address)->toBe('123 Main St')
                ->and($data->city)->toBe('New York')
                ->and($data->country)->toBe('USA');
        });

        it('creates with is_active false', function (): void {
            $data = new WarehouseData(
                name: 'Inactive Warehouse',
                code: 'WH002',
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
            $warehouse = Warehouse::factory()->create([
                'name' => 'Test Warehouse',
                'code' => 'TW001',
                'email' => 'test@warehouse.com',
                'is_active' => true,
            ]);

            $data = WarehouseData::fromModel($warehouse);

            expect($data)->toBeInstanceOf(WarehouseData::class)
                ->and($data->name)->toBe('Test Warehouse')
                ->and($data->code)->toBe('TW001')
                ->and($data->email)->toBe('test@warehouse.com');
        });
    });

    describe('validation', function (): void {
        it('passes validation with valid data', function (): void {
            $validated = WarehouseData::validate([
                'name' => 'Valid Warehouse',
                'code' => 'VW001',
                'email' => 'test@example.com',
                'is_active' => true,
            ]);

            expect($validated['name'])->toBe('Valid Warehouse')
                ->and($validated['code'])->toBe('VW001');
        });

        it('fails validation when name is too short', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => WarehouseData::validate([
                'name' => 'AB',
                'code' => 'WH01',
                'is_active' => true,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when code is too short', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => WarehouseData::validate([
                'name' => 'Valid Name',
                'code' => '',
                'is_active' => true,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation with invalid email', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => WarehouseData::validate([
                'name' => 'Valid Name',
                'code' => 'WH001',
                'email' => 'not-an-email',
                'is_active' => true,
            ]))->toThrow(ValidationException::class);
        });

        it('passes validation with nullable optional fields', function (): void {
            $validated = WarehouseData::validate([
                'name' => 'Test Warehouse',
                'code' => 'TW001',
            ]);

            expect($validated['name'])->toBe('Test Warehouse');
        });
    });
});
