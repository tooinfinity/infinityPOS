<?php

declare(strict_types=1);

namespace App\Actions\Invoices;

use App\Enums\InvoiceStatusEnum;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

final readonly class MarkInvoiceAsPaid
{
    /**
     * Mark an invoice as paid.
     *
     * @throws Throwable
     */
    public function handle(Invoice $invoice, int $userId): Invoice
    {
        if ($invoice->status->isPaid()) {
            return $invoice;
        }

        throw_if(
            $invoice->status->isCancelled(),
            InvalidArgumentException::class,
            'Cannot mark a cancelled invoice as paid.'
        );

        throw_if(
            $invoice->getDue() > 0,
            InvalidArgumentException::class,
            'Invoice still has outstanding balance. Please record payments first.'
        );

        return DB::transaction(function () use ($invoice, $userId): Invoice {
            $invoice->update([
                'status' => InvoiceStatusEnum::PAID,
                'paid_at' => now(),
                'updated_by' => $userId,
            ]);

            return $invoice;
        });
    }
}
