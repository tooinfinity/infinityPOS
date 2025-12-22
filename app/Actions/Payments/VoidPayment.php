<?php

declare(strict_types=1);

namespace App\Actions\Payments;

use App\Data\Payments\RecordMoneyboxTransactionData;
use App\Enums\MoneyboxTransactionTypeEnum;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class VoidPayment
{
    public function __construct(
        private RecordMoneyboxTransaction $recordMoneyboxTransaction,
    ) {}

    /**
     * Void a payment (reverse it completely).
     *
     * @throws Throwable
     */
    public function handle(Payment $payment, int $userId, ?string $reason = null): Payment
    {
        return DB::transaction(function () use ($payment, $userId, $reason): Payment {
            // Create void payment (exact negative of original)
            $voidPayment = Payment::query()->create([
                'reference' => 'VOID-'.$payment->reference,
                'amount' => -$payment->amount,
                'method' => $payment->method,
                'notes' => $this->buildVoidNotes($payment, $reason),
                'related_type' => $payment->related_type,
                'related_id' => $payment->related_id,
                'moneybox_id' => $payment->moneybox_id,
                'created_by' => $userId,
            ]);

            // If original payment had moneybox, reverse the transaction
            if ($payment->moneybox_id !== null) {
                $this->recordMoneyboxTransaction->handle(
                    new RecordMoneyboxTransactionData(
                        moneybox_id: $payment->moneybox_id,
                        type: MoneyboxTransactionTypeEnum::OUT,
                        amount: abs($payment->amount),
                        reference: $voidPayment->reference,
                        notes: $this->buildVoidNotes($payment, $reason),
                        payment_id: $voidPayment->id,
                        expense_id: null,
                        transfer_to_moneybox_id: null,
                        created_by: $userId,
                    )
                );
            }

            return $voidPayment;
        });
    }

    private function buildVoidNotes(Payment $payment, ?string $reason): string
    {
        $notes = 'Void payment '.$payment->reference;

        if ($reason !== null) {
            $notes .= ': '.$reason;
        }

        return $notes;
    }
}
