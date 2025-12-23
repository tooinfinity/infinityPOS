<?php

declare(strict_types=1);

namespace App\Actions\Sales;

use App\Enums\PaymentMethodEnum;
use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class ProcessSalePayment
{
    /**
     * @throws Throwable
     */
    public function handle(
        Sale $sale,
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
            'related_type' => Sale::class,
            'related_id' => $sale->id,
            'moneybox_id' => null,
            'created_by' => $userId,
        ]));
    }
}
