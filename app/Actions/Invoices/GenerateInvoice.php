<?php

declare(strict_types=1);

namespace App\Actions\Invoices;

use App\Data\Invoices\GenerateInvoiceData;
use App\Enums\InvoiceStatusEnum;
use App\Models\Invoice;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

final readonly class GenerateInvoice
{
    /**
     * Generate an invoice from a sale or standalone.
     *
     * @throws Throwable
     */
    public function handle(GenerateInvoiceData $data): Invoice
    {
        $sale = Sale::query()->findOrFail($data->sale_id);

        throw_if(
            $sale->invoice()->exists(),
            InvalidArgumentException::class,
            'Invoice already exists for this sale.'
        );

        return DB::transaction(fn () => Invoice::query()->create([
            'reference' => $data->reference,
            'sale_id' => $data->sale_id,
            'client_id' => $data->client_id ?? $sale->client_id,
            'issued_at' => $data->issued_at,
            'due_at' => $data->due_at,
            'paid_at' => null,
            'subtotal' => $sale->subtotal,
            'discount' => $sale->discount,
            'tax' => $sale->tax,
            'total' => $sale->total,
            'paid' => $sale->paid,
            'status' => InvoiceStatusEnum::PENDING,
            'notes' => $data->notes,
            'created_by' => $data->created_by,
        ]));
    }
}
