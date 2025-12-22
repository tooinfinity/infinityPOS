<?php

declare(strict_types=1);

namespace App\Actions\Invoices;

use App\Enums\InvoiceStatusEnum;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

final readonly class CancelInvoice
{
    /**
     * Cancel an invoice.
     *
     * @throws Throwable
     */
    public function handle(Invoice $invoice, int $userId): Invoice
    {
        if ($invoice->status->isCancelled()) {
            return $invoice;
        }

        throw_if(
            $invoice->status->isPaid(),
            InvalidArgumentException::class,
            'Cannot cancel a paid invoice. Please void the payments first.'
        );

        return DB::transaction(function () use ($invoice, $userId): Invoice {
            $invoice->update([
                'status' => InvoiceStatusEnum::CANCELLED,
                'updated_by' => $userId,
            ]);

            return $invoice;
        });
    }
}
