<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pos;

use App\Actions\Pos\ProcessPosPayment;
use App\Data\Pos\ProcessPosPaymentData;
use Illuminate\Http\JsonResponse;

final readonly class PaymentController
{
    public function store(ProcessPosPaymentData $data, ProcessPosPayment $action): JsonResponse
    {
        $userId = auth()->id();
        abort_if($userId === null, 401);

        $sale = $action->handle($data, (int) $userId);

        return response()->json([
            'data' => [
                'sale_id' => $sale->id,
                'sale_reference' => $sale->reference,
                'status' => $sale->status->value,
            ],
        ], 201);
    }
}
