<?php

declare(strict_types=1);

namespace App\Actions\Invoices;

use App\Data\Invoices\UpdateInvoiceData;
use App\Models\Invoice;
use InvalidArgumentException;
use Throwable;

final readonly class UpdateInvoice
{
    /**
     * Update invoice details (reference, due date, notes).
     *
     * @throws Throwable
     */
    public function handle(Invoice $invoice, UpdateInvoiceData $data): Invoice
    {
        throw_if(
            $invoice->status->isPaid(),
            InvalidArgumentException::class,
            'Cannot update a paid invoice.'
        );

        throw_if(
            $invoice->status->isCancelled(),
            InvalidArgumentException::class,
            'Cannot update a cancelled invoice.'
        );

        $updateData = array_filter([
            'reference' => $data->reference,
            'due_at' => $data->due_at,
            'notes' => $data->notes,
            'updated_by' => $data->updated_by,
        ], fn (mixed $value): bool => $value !== null);

        $invoice->update($updateData);

        return $invoice;
    }
}
