<?php

declare(strict_types=1);

namespace App\Jobs\Documents;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;

final class GenerateInvoicePdfJob implements ShouldQueue
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

        // Ensure storage directory exists
        $storageDir = storage_path('app/invoices');
        if (! file_exists($storageDir) && ! mkdir($storageDir, 0755, true) && ! is_dir($storageDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $storageDir));
        }

        // Generate PDF using DomPDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
        ]);

        // Store PDF with filename: invoice_REFERENCE_TIMESTAMP.pdf
        $filename = sprintf(
            'invoice_%s_%s.pdf',
            $invoice->reference,
            now()->format('YmdHis')
        );

        $filepath = sprintf('%s/%s', $storageDir, $filename);
        $pdf->save($filepath);

        // Optionally, you could store the filepath in a database column or log it
        \Illuminate\Support\Facades\Log::info('Invoice PDF generated', [
            'invoice_id' => $invoice->id,
            'reference' => $invoice->reference,
            'filepath' => $filepath,
        ]);
    }
}
