<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Invoice;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

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
        public string $status,
        public ?string $notes,
        #[Lazy] public ?SaleData $sale,
        #[Lazy] public ?ClientData $client,
        #[Lazy] public ?UserData $creator,
        #[Lazy] public ?UserData $updater,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $created_at,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $updated_at,
    ) {}

    public static function fromModel(Invoice $invoice): self
    {
        return new self(
            id: $invoice->id,
            reference: $invoice->reference,
            issued_at: $invoice->issued_at->toDayDateTimeString(),
            due_at: $invoice->due_at?->toDayDateTimeString(),
            paid_at: $invoice->paid_at?->toDayDateTimeString(),
            subtotal: $invoice->subtotal,
            discount: $invoice->discount,
            tax: $invoice->tax,
            total: $invoice->total,
            paid: $invoice->paid,
            status: $invoice->status,
            notes: $invoice->notes,
            sale: $invoice->sale ? SaleData::from($invoice->sale) : null,
            client: $invoice->client ? ClientData::from($invoice->client) : null,
            creator: $invoice->creator ? UserData::from($invoice->creator) : null,
            updater: $invoice->updater ? UserData::from($invoice->updater) : null,
            created_at: $invoice->created_at?->toDayDateTimeString(),
            updated_at: $invoice->updated_at?->toDayDateTimeString(),
        );
    }
}
