<?php

declare(strict_types=1);

use App\Data\Brand\BrandData;
use App\Models\Brand;
use Illuminate\Validation\ValidationException;

describe(BrandData::class, function (): void {
    describe('creation', function (): void {
        it('creates with required fields', function (): void {
            $data = new BrandData(
                name: 'Test Brand',
                is_active: true,
            );

            expect($data)->toBeInstanceOf(BrandData::class)
                ->and($data->name)->toBe('Test Brand')
                ->and($data->is_active)->toBeTrue();
        });

        it('creates with is_active false', function (): void {
            $data = new BrandData(
                name: 'Inactive Brand',
                is_active: false,
            );

            expect($data->is_active)->toBeFalse();
        });
    });

    describe('fromModel', function (): void {
        it('creates data from model', function (): void {
            $brand = Brand::factory()->create([
                'name' => 'Model Brand',
                'is_active' => true,
            ]);

            $data = BrandData::fromModel($brand);

            expect($data)->toBeInstanceOf(BrandData::class)
                ->and($data->name)->toBe('Model Brand')
                ->and($data->is_active)->toBeTrue();
        });
    });

    describe('validation', function (): void {
        it('passes validation with valid data', function (): void {
            $validated = BrandData::validate([
                'name' => 'Valid Brand Name',
                'is_active' => true,
            ]);

            expect($validated['name'])->toBe('Valid Brand Name')
                ->and($validated['is_active'])->toBeTrue();
        });

        it('fails validation when name is too short', function (): void {
            expect(fn (): Illuminate\Contracts\Support\Arrayable|array => BrandData::validate([
                'name' => 'AB',
                'is_active' => true,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when name exceeds max length', function (): void {
            expect(fn (): Illuminate\Contracts\Support\Arrayable|array => BrandData::validate([
                'name' => str_repeat('a', 81),
                'is_active' => true,
            ]))->toThrow(ValidationException::class);
        });

        it('passes validation with nullable is_active', function (): void {
            $validated = BrandData::validate([
                'name' => 'Test Brand',
            ]);

            expect($validated['name'])->toBe('Test Brand');
        });
    });
});
