<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Invoice;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;

final class InvoiceData extends Data
{
    public function __construct(
        public int $id,
        public string $reference,
        public CarbonInterface $issued_at,
        public ?CarbonInterface $due_at,
        public ?CarbonInterface $paid_at,
        public int $subtotal,
        public ?int $discount,
        public ?int $tax,
        public int $total,
        public int $paid,
        public string $status,
        public ?string $notes,
        public Lazy|SaleData $sale,
        public Lazy|ClientData|null $client,
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        /** @var Lazy|DataCollection<PaymentData> */
        public Lazy|DataCollection $payments,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}

    public static function fromModel(Invoice $invoice): self
    {
        return new self(
            id: $invoice->id,
            reference: $invoice->reference,
            issued_at: $invoice->issued_at,
            due_at: $invoice->due_at,
            paid_at: $invoice->paid_at,
            subtotal: $invoice->subtotal,
            discount: $invoice->discount,
            tax: $invoice->tax,
            total: $invoice->total,
            paid: $invoice->paid,
            status: $invoice->status,
            notes: $invoice->notes,
            sale: Lazy::whenLoaded('sale', $invoice, fn (): SaleData => SaleData::from($invoice->sale)
            ),
            client: Lazy::whenLoaded('client', $invoice, fn (): ?ClientData => $invoice->client ? ClientData::from($invoice->client) : null
            ),
            creator: Lazy::whenLoaded('creator', $invoice, fn (): UserData => UserData::from($invoice->creator)
            ),
            updater: Lazy::whenLoaded('updater', $invoice, fn (): ?UserData => $invoice->updater ? UserData::from($invoice->updater) : null
            ),
            payments: Lazy::whenLoaded('payments', $invoice, fn (): DataCollection => PaymentData::collect($invoice->payments)),
            created_at: $invoice->created_at,
            updated_at: $invoice->updated_at,
        );
    }
}
