<?php

declare(strict_types=1);

namespace App\Actions\Invoices;

use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

final readonly class SendInvoiceEmail
{
    /**
     * Send invoice via email (placeholder implementation).
     *
     * In a real application, this would:
     * - Generate PDF invoice
     * - Queue email job
     * - Send to client email
     * - Log email sent
     *
     * @throws Throwable
     */
    public function handle(Invoice $invoice, string $recipientEmail): bool
    {
        throw_if(
            $invoice->status->isCancelled(),
            InvalidArgumentException::class,
            'Cannot send a cancelled invoice.'
        );

        // Validate email
        throw_if(
            ! filter_var($recipientEmail, FILTER_VALIDATE_EMAIL),
            InvalidArgumentException::class,
            'Invalid email address.'
        );

        // TODO: Implement actual email sending
        // - Generate PDF using invoice data
        // - Queue email job
        // - Send email to recipient
        // - Update invoice sent_at timestamp

        Log::info('Invoice email sent', [
            'invoice_id' => $invoice->id,
            'reference' => $invoice->reference,
            'recipient' => $recipientEmail,
        ]);

        return true;
    }
}
