<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pos;

use App\Services\Pos\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class ApplyCartTaxController
{
    public function __invoke(Request $request, CartService $cartService): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tax' => ['required', 'integer', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid tax amount',
                'errors' => $validator->errors(),
            ], 422);
        }

        /** @var int $taxOverride */
        $taxOverride = $request->input('tax');
        $userId = (int) auth()->id();

        // Store tax override in draft sale
        $cartService->setTaxOverride($userId, $taxOverride);

        return response()->json([
            'message' => 'Tax override applied successfully',
        ]);
    }
}
