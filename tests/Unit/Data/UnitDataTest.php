<?php

declare(strict_types=1);

use App\Data\Unit\UnitData;
use App\Models\Unit;
use Illuminate\Validation\ValidationException;

describe(UnitData::class, function (): void {
    describe('creation', function (): void {
        it('creates with required fields', function (): void {
            $data = new UnitData(
                name: 'Kilogram',
                short_name: 'kg',
                is_active: true,
            );

            expect($data)->toBeInstanceOf(UnitData::class)
                ->and($data->name)->toBe('Kilogram')
                ->and($data->short_name)->toBe('kg')
                ->and($data->is_active)->toBeTrue();
        });

        it('creates with is_active false', function (): void {
            $data = new UnitData(
                name: 'Inactive Unit',
                short_name: 'iu',
                is_active: false,
            );

            expect($data->is_active)->toBeFalse();
        });
    });

    describe('fromModel', function (): void {
        it('creates data from model', function (): void {
            $unit = Unit::factory()->create([
                'name' => 'Piece',
                'short_name' => 'pc',
                'is_active' => true,
            ]);

            $data = UnitData::fromModel($unit);

            expect($data)->toBeInstanceOf(UnitData::class)
                ->and($data->name)->toBe('Piece')
                ->and($data->short_name)->toBe('pc')
                ->and($data->is_active)->toBeTrue();
        });
    });

    describe('validation', function (): void {
        it('passes validation with valid data', function (): void {
            $validated = UnitData::validate([
                'name' => 'Liter',
                'short_name' => 'L',
                'is_active' => true,
            ]);

            expect($validated['name'])->toBe('Liter')
                ->and($validated['short_name'])->toBe('L');
        });

        it('fails validation when name is too short', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => UnitData::validate([
                'name' => 'AB',
                'short_name' => 'a',
                'is_active' => true,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when short_name is too short', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => UnitData::validate([
                'name' => 'Valid Name',
                'short_name' => '',
                'is_active' => true,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when name exceeds max length', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => UnitData::validate([
                'name' => str_repeat('a', 81),
                'short_name' => 'kg',
                'is_active' => true,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when short_name exceeds max length', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => UnitData::validate([
                'name' => 'Valid Name',
                'short_name' => str_repeat('a', 21),
                'is_active' => true,
            ]))->toThrow(ValidationException::class);
        });
    });
});
