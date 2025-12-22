<?php

declare(strict_types=1);

namespace App\Actions\Payments;

use App\Data\Payments\ProcessPaymentData;
use App\Enums\MoneyboxTransactionTypeEnum;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class ProcessPayment
{
    public function __construct(
        private RecordMoneyboxTransaction $recordMoneyboxTransaction,
    ) {}

    /**
     * Process a payment and optionally record it in a moneybox.
     *
     * @throws Throwable
     */
    public function handle(ProcessPaymentData $data): Payment
    {
        return DB::transaction(function () use ($data): Payment {
            $payment = Payment::query()->create([
                'reference' => $data->reference,
                'amount' => $data->amount,
                'method' => $data->method,
                'notes' => $data->notes,
                'related_type' => $data->related_type,
                'related_id' => $data->related_id,
                'moneybox_id' => $data->moneybox_id,
                'created_by' => $data->created_by,
            ]);

            // If moneybox is specified, record the transaction
            if ($data->moneybox_id !== null) {
                $this->recordMoneyboxTransaction->handle(
                    new \App\Data\Payments\RecordMoneyboxTransactionData(
                        moneybox_id: $data->moneybox_id,
                        type: MoneyboxTransactionTypeEnum::IN,
                        amount: $data->amount,
                        reference: $data->reference,
                        notes: $data->notes ?? 'Payment received',
                        payment_id: $payment->id,
                        expense_id: null,
                        transfer_to_moneybox_id: null,
                        created_by: $data->created_by,
                    )
                );
            }

            return $payment;
        });
    }
}
