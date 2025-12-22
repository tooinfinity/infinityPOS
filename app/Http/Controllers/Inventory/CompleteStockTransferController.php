<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Actions\Inventory\CompleteStockTransfer;
use App\Models\StockTransfer;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;
use Throwable;

final readonly class CompleteStockTransferController
{
    /**
     * @throws Throwable
     */
    public function __invoke(StockTransfer $stockTransfer, CompleteStockTransfer $action): RedirectResponse
    {
        try {
            $userId = auth()->id();
            abort_if($userId === null, 401);

            $action->handle($stockTransfer, (int) $userId);

            return back();
        } catch (InvalidArgumentException $invalidArgumentException) {
            return back()->withErrors(['message' => $invalidArgumentException->getMessage()]);
        }
    }
}
