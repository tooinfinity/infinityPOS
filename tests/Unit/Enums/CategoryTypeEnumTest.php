<?php

declare(strict_types=1);

use App\Enums\CategoryTypeEnum;

it('return all categories types', function (): void {
    expect(CategoryTypeEnum::cases())->toBeArray();
});

it('category label', function (): void {
    $value1 = 'Product';
    $value2 = 'Expense';
    expect(CategoryTypeEnum::PRODUCT->label())->toBe($value1)
        ->and(CategoryTypeEnum::EXPENSE->label())->toBe($value2);
});

it('category icon', function (): void {
    $value1 = 'cube';
    $value2 = 'receipt';
    expect(CategoryTypeEnum::PRODUCT->icon())->toBe($value1)
        ->and(CategoryTypeEnum::EXPENSE->icon())->toBe($value2);
});

it('category to array', function (): void {
    expect(CategoryTypeEnum::toArray())->toBeArray();
});
