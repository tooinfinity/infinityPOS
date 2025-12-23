<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pos;

use App\Actions\Pos\CalculateCartTotals;
use App\Actions\Pos\ClearCart;
use App\Services\Pos\CartService;
use App\Traits\PresentsCart;
use Illuminate\Http\JsonResponse;

final readonly class ClearCartController
{
    use PresentsCart;

    public function __construct(
        private CartService $cart,
        private CalculateCartTotals $totals,
    ) {}

    public function __invoke(ClearCart $action): JsonResponse
    {
        $userId = auth()->id();
        abort_if($userId === null, 401);

        $action->handle((int) $userId);

        return response()->json([
            'data' => $this->present($this->cart, $this->totals),
        ]);
    }
}
