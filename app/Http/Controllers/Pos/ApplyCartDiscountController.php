<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pos;

use App\Actions\Pos\ApplyDiscount;
use App\Actions\Pos\CalculateCartTotals;
use App\Data\Pos\ApplyCartDiscountData;
use App\Services\Pos\CartService;
use App\Traits\PresentsCart;
use Illuminate\Http\JsonResponse;

final readonly class ApplyCartDiscountController
{
    use PresentsCart;

    public function __construct(
        private CartService $cart,
        private CalculateCartTotals $totals,
    ) {}

    public function __invoke(ApplyCartDiscountData $data, ApplyDiscount $action): JsonResponse
    {
        $userId = auth()->id();
        abort_if($userId === null, 401);

        $action->handle($data, (int) $userId);

        return response()->json([
            'data' => $this->present($this->cart, $this->totals),
        ]);
    }
}
