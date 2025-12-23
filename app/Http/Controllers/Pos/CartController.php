<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pos;

use App\Actions\Pos\CalculateCartTotals;
use App\Services\Pos\CartService;
use App\Traits\PresentsCart;
use Illuminate\Http\JsonResponse;

final readonly class CartController
{
    use PresentsCart;

    public function __construct(
        private CartService $cart,
        private CalculateCartTotals $totals,
    ) {}

    public function show(): JsonResponse
    {
        return response()->json([
            'data' => $this->present($this->cart, $this->totals),
        ]);
    }
}
