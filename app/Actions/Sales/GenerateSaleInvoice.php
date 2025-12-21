<?php

declare(strict_types=1);

namespace App\Actions\Sales;

use App\Enums\InvoiceStatusEnum;
use App\Models\Invoice;
use App\Models\Sale;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

final readonly class GenerateSaleInvoice
{
    /**
     * @throws Throwable
     */
    public function handle(
        Sale $sale,
        string $reference,
        DateTimeInterface $issuedAt,
        ?DateTimeInterface $dueAt = null,
        ?string $notes = null,
        ?int $userId = null
    ): Invoice {
        throw_if($sale->invoice()->exists(), InvalidArgumentException::class, 'Invoice already exists for this sale.');

        return DB::transaction(fn () => Invoice::query()->create([
            'reference' => $reference,
            'sale_id' => $sale->id,
            'client_id' => $sale->client_id,
            'issued_at' => $issuedAt,
            'due_at' => $dueAt,
            'paid_at' => null,
            'subtotal' => $sale->subtotal,
            'discount' => $sale->discount,
            'tax' => $sale->tax,
            'total' => $sale->total,
            'paid' => $sale->paid,
            'status' => InvoiceStatusEnum::PENDING,
            'notes' => $notes,
            'created_by' => $userId,
        ]));
    }
}
