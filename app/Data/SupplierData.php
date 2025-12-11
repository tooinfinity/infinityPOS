<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

final class SupplierData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $phone,
        public ?string $email,
        public ?string $address,
        public int $balance,
        public bool $is_active,
        public Lazy|BusinessIdentifierData|null $businessIdentifier,
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}
}
