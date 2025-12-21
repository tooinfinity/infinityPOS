<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sales;

use App\Actions\Sales\CompleteSaleReturn;
use App\Models\SaleReturn;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;
use Throwable;

final readonly class CompleteSaleReturnController
{
    /**
     * @throws Throwable
     */
    public function __invoke(SaleReturn $saleReturn, CompleteSaleReturn $action): RedirectResponse
    {
        try {
            $userId = auth()->id();
            abort_if($userId === null, 401);

            $action->handle($saleReturn, (int) $userId);

            return back();
        } catch (InvalidArgumentException $invalidArgumentException) {
            return back()->withErrors(['message' => $invalidArgumentException->getMessage()]);
        }
    }
}
