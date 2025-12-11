<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Company;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
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
        #[Lazy] public ?BusinessIdentifierData $businessIdentifier,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $created_at,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $updated_at,
    ) {}

    public static function fromModel(Company $company): self
    {
        return new self(
            id: $company->id,
            name: $company->name,
            email: $company->email,
            phone: $company->phone,
            phone_secondary: $company->phone_secondary,
            address: $company->address,
            city: $company->city,
            state: $company->state,
            zip: $company->zip,
            country: $company->country,
            logo: $company->logo,
            website: $company->website,
            description: $company->description,
            currency: $company->currency,
            currency_symbol: $company->currency_symbol,
            timezone: $company->timezone,
            date_format: $company->date_format,
            businessIdentifier: $company->businessIdentifier ? BusinessIdentifierData::from($company->businessIdentifier) : null,
            created_at: $company->created_at?->toDayDateTimeString(),
            updated_at: $company->updated_at?->toDayDateTimeString(),
        );
    }
}
