<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pos;

use App\Actions\Pos\CalculateCartTotals;
use App\Actions\Pos\RemoveProductFromCart;
use App\Services\Pos\CartService;
use App\Traits\PresentsCart;
use Illuminate\Http\JsonResponse;

final readonly class RemoveCartItemController
{
    use PresentsCart;

    public function __construct(
        private CartService $cart,
        private CalculateCartTotals $totals,
    ) {}

    public function __invoke(string $lineId, RemoveProductFromCart $action): JsonResponse
    {
        $userId = auth()->id();
        abort_if($userId === null, 401);

        $action->handle($lineId);

        return response()->json([
            'data' => $this->present($this->cart, $this->totals),
        ]);
    }
}
