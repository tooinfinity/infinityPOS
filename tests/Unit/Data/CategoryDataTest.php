<?php

declare(strict_types=1);

use App\Data\Category\CategoryData;
use App\Models\Category;
use Illuminate\Validation\ValidationException;

describe(CategoryData::class, function (): void {
    describe('creation', function (): void {
        it('creates with required fields', function (): void {
            $data = new CategoryData(
                name: 'Electronics',
                description: null,
                is_active: true,
            );

            expect($data)->toBeInstanceOf(CategoryData::class)
                ->and($data->name)->toBe('Electronics')
                ->and($data->description)->toBeNull()
                ->and($data->is_active)->toBeTrue();
        });

        it('creates with all optional fields', function (): void {
            $data = new CategoryData(
                name: 'Electronics',
                description: 'Electronic items and gadgets',
                is_active: true,
            );

            expect($data->description)->toBe('Electronic items and gadgets');
        });

        it('creates with is_active false', function (): void {
            $data = new CategoryData(
                name: 'Inactive Category',
                description: null,
                is_active: false,
            );

            expect($data->is_active)->toBeFalse();
        });
    });

    describe('fromModel', function (): void {
        it('creates data from model', function (): void {
            $category = Category::factory()->create([
                'name' => 'Test Category',
                'description' => 'Test Description',
                'is_active' => true,
            ]);

            $data = CategoryData::fromModel($category);

            expect($data)->toBeInstanceOf(CategoryData::class)
                ->and($data->name)->toBe('Test Category')
                ->and($data->description)->toBe('Test Description')
                ->and($data->is_active)->toBeTrue();
        });
    });

    describe('validation', function (): void {
        it('passes validation with valid data', function (): void {
            $validated = CategoryData::validate([
                'name' => 'Valid Category',
                'description' => 'Test description',
                'is_active' => true,
            ]);

            expect($validated['name'])->toBe('Valid Category');
        });

        it('fails validation when name is too short', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => CategoryData::validate([
                'name' => 'AB',
                'is_active' => true,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when name exceeds max length', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => CategoryData::validate([
                'name' => str_repeat('a', 81),
                'is_active' => true,
            ]))->toThrow(ValidationException::class);
        });

        it('fails validation when description exceeds max length', function (): void {
            expect(fn (): array|\Illuminate\Contracts\Support\Arrayable => CategoryData::validate([
                'name' => 'Valid Name',
                'description' => str_repeat('a', 256),
                'is_active' => true,
            ]))->toThrow(ValidationException::class);
        });

        it('passes validation with nullable description', function (): void {
            $validated = CategoryData::validate([
                'name' => 'Test Category',
            ]);

            expect($validated['name'])->toBe('Test Category');
        });
    });
});
