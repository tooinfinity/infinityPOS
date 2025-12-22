<?php

declare(strict_types=1);

namespace App\Http\Controllers\Invoices;

use App\Actions\Invoices\MarkInvoiceAsPaid;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;
use Throwable;

final readonly class MarkInvoiceAsPaidController
{
    /**
     * Mark invoice as paid.
     *
     * @throws Throwable
     */
    public function __invoke(Invoice $invoice, MarkInvoiceAsPaid $action): RedirectResponse
    {
        try {
            $userId = auth()->id();
            abort_if($userId === null, 401);

            $action->handle($invoice, (int) $userId);

            return back();
        } catch (InvalidArgumentException $invalidArgumentException) {
            return back()->withErrors(['message' => $invalidArgumentException->getMessage()]);
        }
    }
}
