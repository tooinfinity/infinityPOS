<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Actions\StockTransfer\CompleteStockTransfer;
use App\Models\StockTransfer;
use Illuminate\Http\RedirectResponse;
use Throwable;

final class CompleteStockTransferController
{
    /**
     * Handle the incoming request.
     *
     * @throws Throwable
     */
    public function __invoke(StockTransfer $stockTransfer, CompleteStockTransfer $action): RedirectResponse
    {
        $action->handle($stockTransfer);

        return to_route('stock-transfers.show', $stockTransfer)
            ->with('success', "Transfer {$stockTransfer->reference_no} completed. Stock moved.");
    }
}
