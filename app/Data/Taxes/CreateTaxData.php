<?php

declare(strict_types=1);

namespace App\Data\Taxes;

use App\Enums\TaxTypeEnum;
use Spatie\LaravelData\Data;

final class CreateTaxData extends Data
{
    public function __construct(
        public string $name,
        public int $rate,
        public TaxTypeEnum $tax_type,
        public bool $is_active,
        public int $created_by,
    ) {}
}
