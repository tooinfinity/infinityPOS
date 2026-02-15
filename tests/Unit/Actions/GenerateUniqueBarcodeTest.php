<?php

declare(strict_types=1);

use App\Actions\GenerateUniqueBarcode;
use App\Models\Product;

it('generates valid EAN-13 barcode with 13 digits', function (): void {
    $action = resolve(GenerateUniqueBarcode::class);

    $barcode = $action->handle();

    expect($barcode)
        ->toHaveLength(13)
        ->and(is_numeric($barcode))->toBeTrue();
});

it('generates EAN-13 barcode starting with 978 prefix', function (): void {
    $action = resolve(GenerateUniqueBarcode::class);

    $barcode = $action->handle();

    expect($barcode)->toStartWith('978');
});

it('generates valid EAN-13 check digit', function (): void {
    $action = resolve(GenerateUniqueBarcode::class);

    $barcode = $action->handle();

    $sum = 0;
    for ($i = 0; $i < 12; $i++) {
        $digit = (int) $barcode[$i];
        $sum += $i % 2 === 0 ? $digit : $digit * 3;
    }

    $expectedCheckDigit = (10 - ($sum % 10)) % 10;
    $actualCheckDigit = (int) $barcode[12];

    expect($actualCheckDigit)->toBe($expectedCheckDigit);
});

it('generates different barcodes on consecutive calls', function (): void {
    $action = resolve(GenerateUniqueBarcode::class);

    $barcode1 = $action->handle();
    $barcode2 = $action->handle();

    expect($barcode1)->not->toBe($barcode2);
});

it('ensures uniqueness when duplicate exists', function (): void {
    Product::factory()->create(['barcode' => '9781234567890']);

    $action = resolve(GenerateUniqueBarcode::class);

    $barcode = $action->handle();

    expect($barcode)->not->toBe('9781234567890')
        ->and(Product::query()->where('barcode', $barcode)->exists())->toBeFalse();
});

it('generates only numeric characters', function (): void {
    $action = resolve(GenerateUniqueBarcode::class);

    $barcode = $action->handle();

    expect(preg_match('/^\d{13}$/', $barcode))->toBe(1);
});

it('handles multiple existing barcodes', function (): void {
    Product::factory()->create(['barcode' => '9780000000000']);
    Product::factory()->create(['barcode' => '9781111111117']);
    Product::factory()->create(['barcode' => '9782222222224']);

    $action = resolve(GenerateUniqueBarcode::class);

    $barcode = $action->handle();

    expect($barcode)->not->toBeIn(['9780000000000', '9781111111117', '9782222222224'])
        ->and(Product::query()->where('barcode', $barcode)->exists())->toBeFalse();
});
