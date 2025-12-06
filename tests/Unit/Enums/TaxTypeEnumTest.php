<?php

declare(strict_types=1);

use App\Enums\TaxTypeEnum;

it('return all taxes types', function (): void {
    expect(TaxTypeEnum::cases())->toBeArray();
});

it('tax label', function (): void {
    $value1 = 'Fixed';
    $value2 = 'Percentage';
    expect(TaxTypeEnum::FIXED->label())->toBe($value1)
        ->and(TaxTypeEnum::PERCENTAGE->label())->toBe($value2);
});

it('tax symbol', function (): void {
    $value1 = '';
    $value2 = '%';
    expect(TaxTypeEnum::FIXED->symbol())->toBe($value1)
        ->and(TaxTypeEnum::PERCENTAGE->symbol())->toBe($value2);
});

it('tax to array', function (): void {
    expect(TaxTypeEnum::toArray())->toBeArray();
});
