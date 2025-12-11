<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonInterface;
use App\\Enums\\TaxTypeEnum;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;

final class TaxData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public TaxTypeEnum $tax_type,
        public int $rate,
        public bool $is_active,
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}
}
