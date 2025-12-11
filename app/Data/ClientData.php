<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Client;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

final class ClientData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $phone,
        public ?string $email,
        public ?string $address,
        public int $balance,
        public bool $is_active,
        #[Lazy] public ?BusinessIdentifierData $businessIdentifier,
        #[Lazy] public ?UserData $creator,
        #[Lazy] public ?UserData $updater,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $created_at,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $updated_at,
    ) {}

    public static function fromModel(Client $client): self
    {
        return new self(
            id: $client->id,
            name: $client->name,
            phone: $client->phone,
            email: $client->email,
            address: $client->address,
            balance: $client->balance,
            is_active: $client->is_active,
            businessIdentifier: $client->businessIdentifier ? BusinessIdentifierData::from($client->businessIdentifier) : null,
            creator: $client->creator ? UserData::from($client->creator) : null,
            updater: $client->updater ? UserData::from($client->updater) : null,
            created_at: $client->created_at?->toDayDateTimeString(),
            updated_at: $client->updated_at?->toDayDateTimeString(),
        );
    }
}
