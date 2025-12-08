<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\BusinessIdentifier;
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

    public static function fromModel(BusinessIdentifier $identifier): self
    {
        return new self(
            id: $identifier->id,
            article: $identifier->article,
            nif: $identifier->nif,
            nis: $identifier->nis,
            rc: $identifier->rc,
            rib: $identifier->rib,
            created_at: $identifier->created_at,
            updated_at: $identifier->updated_at,
        );
    }
}
