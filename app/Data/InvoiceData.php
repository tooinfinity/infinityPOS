<?php

declare(strict_types=1);

namespace App\Data;

use App\Data\Clients\ClientData;
use App\Data\Users\UserData;
use App\Enums\InvoiceStatusEnum;
use Spatie\LaravelData\Attributes\AutoLazy;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

#[AutoLazy]
final class InvoiceData extends Data
{
    public function __construct(
        public int $id,
        public string $reference,
        public string $issued_at,
        public ?string $due_at,
        public ?string $paid_at,
        public int $subtotal,
        public ?int $discount,
        public ?int $tax,
        public int $total,
        public int $paid,
        public InvoiceStatusEnum $status,
        public ?string $notes,
        public Lazy|SaleData|null $sale,
        public Lazy|ClientData|null $client,
        public Lazy|UserData|null $creator,
        public Lazy|UserData|null $updater,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $created_at,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $updated_at,
    ) {}
}
