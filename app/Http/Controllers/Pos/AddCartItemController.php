<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pos;

use App\Actions\Pos\AddProductToCart;
use App\Actions\Pos\CalculateCartTotals;
use App\Data\Pos\AddProductToCartData;
use App\Services\Pos\CartService;
use App\Traits\PresentsCart;
use Illuminate\Http\JsonResponse;

final readonly class AddCartItemController
{
    use PresentsCart;

    public function __construct(
        private CartService $cart,
        private CalculateCartTotals $totals,
    ) {}

    public function __invoke(AddProductToCartData $data, AddProductToCart $action): JsonResponse
    {
        $userId = auth()->id();
        abort_if($userId === null, 401);

        $action->handle($data, (int) $userId);

        return response()->json([
            'data' => $this->present($this->cart, $this->totals),
        ], 201);
    }
}
