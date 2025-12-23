<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pos;

use App\Data\PaymentData;
use App\Data\SaleData;
use App\Data\SaleItemData;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;

final class ReceiptController
{
    public function show(Sale $sale): JsonResponse
    {
        $sale->load([
            'store',
            'client',
            'creator',
            'items.product.tax',
            'payments',
        ]);

        return response()->json([
            'data' => [
                'sale' => SaleData::from($sale),
                'items' => SaleItemData::collect($sale->items),
                'payments' => PaymentData::collect($sale->payments),
                'totals' => [
                    'subtotal' => (int) $sale->subtotal,
                    'discount' => (int) ($sale->discount ?? 0),
                    'tax' => (int) ($sale->tax ?? 0),
                    'total' => (int) $sale->total,
                    'paid' => $sale->getPaid(),
                    'due' => $sale->getDue(),
                ],
            ],
        ]);
    }
}
