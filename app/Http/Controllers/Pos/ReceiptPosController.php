<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pos;

use App\Models\Sale;
use Inertia\Inertia;
use Inertia\Response;

final class ReceiptPosController
{
    public function __invoke(Sale $sale): Response
    {
        $sale->load([
            'items.product.unit',
            'items.batch',
            'customer',
            'warehouse',
            'payments.paymentMethod',
            'user',
        ]);

        return Inertia::render('pos/receipt', [
            'sale' => $sale,
            'changeAmount' => session('change_amount', 0),
        ]);
    }
}
