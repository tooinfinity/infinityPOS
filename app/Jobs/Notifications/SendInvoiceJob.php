<?php

declare(strict_types=1);

namespace App\Jobs\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class SendInvoiceJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly int $invoiceId,
    ) {}

    public function handle(): void
    {
        $invoice = \App\Models\Invoice::query()
            ->with(['sale.items.product', 'client'])
            ->findOrFail($this->invoiceId);

        // Only send if client exists and has an email
        if ($invoice->client === null || empty($invoice->client->email)) {
            \Illuminate\Support\Facades\Log::warning('Cannot send invoice: no client or email', [
                'invoice_id' => $invoice->id,
                'reference' => $invoice->reference,
            ]);

            return;
        }

        // Look for the most recent PDF for this invoice
        $storageDir = storage_path('app/invoices');
        $pdfPath = null;

        if (file_exists($storageDir)) {
            $files = glob(sprintf('%s/invoice_%s_*.pdf', $storageDir, $invoice->reference));
            if ($files !== [] && $files !== false) {
                // Get the most recent file
                usort($files, fn (string $a, string $b): int => filemtime($b) <=> filemtime($a));
                $pdfPath = $files[0];
            }
        }

        // Send email with invoice
        \Illuminate\Support\Facades\Mail::to($invoice->client->email)
            ->send(new \App\Mail\InvoiceMail($invoice, $pdfPath));

        \Illuminate\Support\Facades\Log::info('Invoice sent to client', [
            'invoice_id' => $invoice->id,
            'reference' => $invoice->reference,
            'client_email' => $invoice->client->email,
            'pdf_attached' => $pdfPath !== null,
        ]);
    }
}
