<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Actions\StockTransfer\CancelStockTransfer;
use App\Models\StockTransfer;
use Illuminate\Http\RedirectResponse;
use Throwable;

final class CancelStockTransferController
{
    /**
     * @throws Throwable
     */
    public function __invoke(StockTransfer $stockTransfer, CancelStockTransfer $action): RedirectResponse
    {
        $action->handle($stockTransfer);

        return to_route('stock-transfers.show', $stockTransfer)
            ->with('success', "Transfer {$stockTransfer->reference_no} cancelled.");
    }
}
