<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Client;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
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
        public Lazy|BusinessIdentifierData|null $businessIdentifier,
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        /** @var Lazy|DataCollection<SaleData> */
        public Lazy|DataCollection $sales,
        /** @var Lazy|DataCollection<SaleReturnData> */
        public Lazy|DataCollection $saleReturns,
        /** @var Lazy|DataCollection<InvoiceData> */
        public Lazy|DataCollection $invoices,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
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
            businessIdentifier: Lazy::whenLoaded('businessIdentifier', $client, fn (): ?BusinessIdentifierData => $client->businessIdentifier ? BusinessIdentifierData::from($client->businessIdentifier) : null),
            creator: Lazy::whenLoaded('creator', $client, fn (): UserData => UserData::from($client->creator)
            ),
            updater: Lazy::whenLoaded('updater', $client, fn (): ?UserData => $client->updater ? UserData::from($client->updater) : null
            ),
            sales: Lazy::whenLoaded('sales', $client, fn (): DataCollection => SaleData::collect($client->sales)),
            saleReturns: Lazy::whenLoaded('saleReturns', $client, fn (): DataCollection => SaleReturnData::collect($client->saleReturns)),
            invoices: Lazy::whenLoaded('invoices', $client, fn (): DataCollection => InvoiceData::collect($client->invoices)),
            created_at: $client->created_at,
            updated_at: $client->updated_at,
        );
    }
}
