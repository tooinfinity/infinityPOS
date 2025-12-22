<?php

declare(strict_types=1);

namespace App\Actions\Payments;

use App\Data\Payments\RecordMoneyboxTransactionData;
use App\Data\Payments\RefundPaymentData;
use App\Enums\MoneyboxTransactionTypeEnum;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

final readonly class RefundPayment
{
    public function __construct(
        private RecordMoneyboxTransaction $recordMoneyboxTransaction,
    ) {}

    /**
     * Process a refund for an existing payment.
     *
     * @throws Throwable
     */
    public function handle(RefundPaymentData $data): Payment
    {
        $originalPayment = Payment::query()->findOrFail($data->original_payment_id);

        throw_if(
            $data->amount > $originalPayment->amount,
            InvalidArgumentException::class,
            'Refund amount cannot exceed original payment amount.'
        );

        return DB::transaction(function () use ($data, $originalPayment): Payment {
            // Create refund payment (negative amount)
            $refundPayment = Payment::query()->create([
                'reference' => 'REFUND-'.$originalPayment->reference,
                'amount' => -$data->amount,
                'method' => $originalPayment->method,
                'notes' => $this->buildRefundNotes($data),
                'related_type' => $originalPayment->related_type,
                'related_id' => $originalPayment->related_id,
                'moneybox_id' => $data->moneybox_id ?? $originalPayment->moneybox_id,
                'created_by' => $data->created_by,
            ]);

            // If moneybox is specified, record the outgoing transaction
            $moneyboxId = $data->moneybox_id ?? $originalPayment->moneybox_id;
            if ($moneyboxId !== null) {
                $this->recordMoneyboxTransaction->handle(
                    new RecordMoneyboxTransactionData(
                        moneybox_id: $moneyboxId,
                        type: MoneyboxTransactionTypeEnum::OUT,
                        amount: $data->amount,
                        reference: $refundPayment->reference,
                        notes: $this->buildRefundNotes($data),
                        payment_id: $refundPayment->id,
                        expense_id: null,
                        transfer_to_moneybox_id: null,
                        created_by: $data->created_by,
                    )
                );
            }

            return $refundPayment;
        });
    }

    private function buildRefundNotes(RefundPaymentData $data): string
    {
        $notes = 'Refund';

        if ($data->reason !== null) {
            $notes .= ': '.$data->reason;
        }

        if ($data->notes !== null) {
            $notes .= ' - '.$data->notes;
        }

        return $notes;
    }
}
