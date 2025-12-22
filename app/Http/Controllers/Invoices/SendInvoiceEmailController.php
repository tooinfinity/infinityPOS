<?php

declare(strict_types=1);

namespace App\Http\Controllers\Invoices;

use App\Actions\Invoices\SendInvoiceEmail;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final readonly class SendInvoiceEmailController
{
    /**
     * Send invoice via email.
     */
    public function __invoke(Request $request, Invoice $invoice, SendInvoiceEmail $action): RedirectResponse
    {
        try {
            /** @var string $email */
            $email = $request->input('email');

            $action->handle($invoice, $email);

            return back()->with('message', 'Invoice email sent successfully.');
        } catch (InvalidArgumentException $invalidArgumentException) {
            return back()->withErrors(['message' => $invalidArgumentException->getMessage()]);
        }
    }
}
