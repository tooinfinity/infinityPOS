<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\BusinessIdentifier;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
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
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $created_at,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $updated_at,
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
            created_at: $identifier->created_at?->toDayDateTimeString(),
            updated_at: $identifier->updated_at?->toDayDateTimeString(),
        );
    }
}
