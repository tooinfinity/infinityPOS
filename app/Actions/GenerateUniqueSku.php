<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Product;
use Illuminate\Support\Str;

final readonly class GenerateUniqueSku
{
    public function handle(): string
    {
        do {
            $sku = 'PRD-'.mb_strtoupper(Str::random(6));
        } while ($this->skuExists($sku));

        return $sku;
    }

    private function skuExists(string $sku): bool
    {
        return Product::query()->where('sku', $sku)->exists();
    }
}
