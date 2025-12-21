<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sales;

use App\Actions\Sales\GenerateSaleInvoice;
use App\Data\Sales\GenerateSaleInvoiceData;
use App\Models\Sale;
use DateTimeImmutable;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;
use Throwable;

final readonly class SaleInvoiceController
{
    /**
     * @throws Throwable
     */
    public function store(GenerateSaleInvoiceData $data, Sale $sale, GenerateSaleInvoice $action): RedirectResponse
    {
        $userId = auth()->id();
        abort_if($userId === null, 401);

        try {
            $action->handle(
                sale: $sale,
                reference: $data->reference,
                issuedAt: new DateTimeImmutable($data->issued_at),
                dueAt: $data->due_at !== null ? new DateTimeImmutable($data->due_at) : null,
                notes: $data->notes,
                userId: (int) $userId,
            );

            return back();
        } catch (InvalidArgumentException $invalidArgumentException) {
            return back()->withErrors(['message' => $invalidArgumentException->getMessage()]);
        }
    }
}
