<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Product;
use Random\RandomException;

final readonly class GenerateUniqueBarcode
{
    /**
     * @throws RandomException
     */
    public function handle(): string
    {
        do {
            $barcode = $this->generateEan13();
        } while ($this->barcodeExists($barcode));

        return $barcode;
    }

    /**
     * @throws RandomException
     */
    private function generateEan13(): string
    {
        $prefix = '978';
        $randomDigits = mb_str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);
        $barcodeWithoutCheck = $prefix.$randomDigits;

        return $barcodeWithoutCheck.$this->calculateCheckDigit($barcodeWithoutCheck);
    }

    private function calculateCheckDigit(string $barcode): int
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = (int) $barcode[$i];
            $sum += $i % 2 === 0 ? $digit : $digit * 3;
        }

        return (10 - ($sum % 10)) % 10;
    }

    private function barcodeExists(string $barcode): bool
    {
        return Product::query()->where('barcode', $barcode)->exists();
    }
}
