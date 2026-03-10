<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pos;

use App\Actions\Pos\ProcessPosOrder;
use App\Data\Pos\PosOrderData;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final readonly class PosController
{
    public function index(): Response
    {
        return Inertia::render('pos/index', [
            'warehouses' => Warehouse::query()->select('id', 'name', 'code')->get(),
            'paymentMethods' => PaymentMethod::query()->select('id', 'name', 'code')->get(),
            'customers' => Customer::query()
                ->select('id', 'name', 'phone')
                ->get(),
            'defaultWarehouseId' => Warehouse::query()->value('id'),
        ]);
    }

    /**
     * Process the POS order.
     *
     * @throws Throwable
     */
    public function store(PosOrderData $data, ProcessPosOrder $action): RedirectResponse
    {
        $result = $action->handle($data);

        return to_route('pos.receipt', $result->sale)
            ->with('change_amount', $result->changeAmount)
            ->with('success', "Sale {$result->sale->reference_no} completed.");
    }
}
