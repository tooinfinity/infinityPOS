<?php

declare(strict_types=1);

namespace App\Http\Controllers\Invoices;

use App\Actions\Invoices\GenerateInvoice;
use App\Actions\Invoices\UpdateInvoice;
use App\Data\InvoiceData;
use App\Data\Invoices\GenerateInvoiceData;
use App\Data\Invoices\UpdateInvoiceData;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;
use Throwable;

final readonly class InvoiceController
{
    public function index(): Response
    {
        $invoices = Invoice::query()
            ->with(['sale', 'client', 'creator'])
            ->latest()
            ->paginate(50);

        return Inertia::render('invoices/index', [
            'invoices' => InvoiceData::collect($invoices),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('invoices/create');
    }

    /**
     * @throws Throwable
     */
    public function store(GenerateInvoiceData $data, GenerateInvoice $action): RedirectResponse
    {
        try {
            $invoice = $action->handle($data);

            return to_route('invoices.show', $invoice);
        } catch (InvalidArgumentException $invalidArgumentException) {
            return back()->withErrors(['message' => $invalidArgumentException->getMessage()]);
        }
    }

    public function show(Invoice $invoice): Response
    {
        $invoice->load(['sale.items.product', 'client', 'creator', 'payments']);

        return Inertia::render('invoices/show', [
            'invoice' => InvoiceData::from($invoice),
        ]);
    }

    public function edit(Invoice $invoice): Response
    {
        $invoice->load(['sale', 'client']);

        return Inertia::render('invoices/edit', [
            'invoice' => InvoiceData::from($invoice),
        ]);
    }

    public function update(UpdateInvoiceData $data, Invoice $invoice, UpdateInvoice $action): RedirectResponse
    {
        try {
            $action->handle($invoice, $data);

            return back();
        } catch (InvalidArgumentException $invalidArgumentException) {
            return back()->withErrors(['message' => $invalidArgumentException->getMessage()]);
        }
    }
}
