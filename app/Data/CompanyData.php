<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

final class CompanyData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $email,
        public ?string $phone,
        public ?string $phone_secondary,
        public ?string $address,
        public ?string $city,
        public ?string $state,
        public ?string $zip,
        public ?string $country,
        public ?string $logo,
        public ?string $website,
        public ?string $description,
        public string $currency,
        public string $currency_symbol,
        public string $timezone,
        public string $date_format,
        public Lazy|BusinessIdentifierData|null $businessIdentifier,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}
}
