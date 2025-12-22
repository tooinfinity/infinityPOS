<?php

declare(strict_types=1);

namespace App\Actions\Purchases;

use App\Enums\PaymentMethodEnum;
use App\Models\Payment;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class ProcessPurchasePayment
{
    /**
     * @throws Throwable
     */
    public function handle(
        Purchase $purchase,
        int $amount,
        PaymentMethodEnum $method,
        ?string $reference = null,
        ?string $notes = null,
        ?int $userId = null
    ): Payment {
        return DB::transaction(fn () => Payment::query()->create([
            'reference' => $reference,
            'amount' => $amount,
            'method' => $method,
            'notes' => $notes,
            'related_type' => Purchase::class,
            'related_id' => $purchase->id,
            'created_by' => $userId,
        ]));
    }
}
