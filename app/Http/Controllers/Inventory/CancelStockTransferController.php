<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Actions\Inventory\CancelStockTransfer;
use App\Models\StockTransfer;
use Illuminate\Http\RedirectResponse;
use Throwable;

final readonly class CancelStockTransferController
{
    /**
     * @throws Throwable
     */
    public function __invoke(StockTransfer $stockTransfer, CancelStockTransfer $action): RedirectResponse
    {
        $userId = auth()->id();
        abort_if($userId === null, 401);

        $action->handle($stockTransfer, (int) $userId);

        return back();
    }
}
