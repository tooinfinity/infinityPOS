<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;

final class BusinessIdentifierData extends Data
{
    public function __construct(
        public int $id,
        public ?string $article,
        public ?string $nif,
        public ?string $nis,
        public ?string $rc,
        public ?string $rib,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}
}
