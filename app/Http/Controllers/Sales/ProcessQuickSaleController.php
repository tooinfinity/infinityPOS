<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sales;

use App\Actions\Sale\QuickSale as QuickSaleAction;
use App\Data\Sale\CreateSaleData;
use App\Data\Sale\QuickSaleData;
use App\Http\Requests\Sale\StoreSaleRequest;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Throwable;

final readonly class ProcessQuickSaleController
{
    /**
     * @throws Throwable
     */
    public function __invoke(StoreSaleRequest $request, #[CurrentUser] User $user, QuickSaleAction $quickSale): RedirectResponse
    {
        $data = CreateSaleData::from($request->validated());
        $data->user_id = $user->id;

        $quickSaleData = QuickSaleData::from([
            'customer_id' => $data->customer_id,
            'warehouse_id' => $data->warehouse_id,
            'user_id' => $data->user_id,
            'sale_date' => $data->sale_date,
            'note' => $data->note,
            'items' => $data->items,
            'paid_amount' => $data->paid_amount ?? 0,
            'payment_method_id' => $request->input('payment_method_id'),
        ]);

        $quickSale->handle($quickSaleData);

        return to_route('sales.index');
    }
}
