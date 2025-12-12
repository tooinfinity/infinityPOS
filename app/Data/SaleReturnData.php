<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Attributes\AutoLazy;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

#[AutoLazy]
final class SaleReturnData extends Data
{
    public function __construct(
        public int $id,
        public string $reference,
        public int $subtotal,
        public ?int $discount,
        public ?int $tax,
        public int $total,
        public int $refunded,
        public string $status,
        public ?string $reason,
        public ?string $notes,
        public Lazy|SaleData|null $sale,
        public Lazy|ClientData|null $client,
        public Lazy|StoreData|null $store,
        public Lazy|UserData|null $creator,
        public Lazy|UserData|null $updater,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $created_at,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $updated_at,
    ) {}
}
