<?php

declare(strict_types=1);

namespace App\Actions\Payments;

use App\Data\Payments\RecordMoneyboxTransactionData;
use App\Data\Payments\TransferBetweenMoneyboxesData;
use App\Enums\MoneyboxTransactionTypeEnum;
use App\Models\MoneyboxTransaction;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

final readonly class TransferBetweenMoneyboxes
{
    public function __construct(
        private RecordMoneyboxTransaction $recordMoneyboxTransaction,
    ) {}

    /**
     * Transfer amount between two moneyboxes.
     *
     * @return array{from: MoneyboxTransaction, to: MoneyboxTransaction}
     *
     * @throws Throwable
     */
    public function handle(TransferBetweenMoneyboxesData $data): array
    {
        throw_if(
            $data->from_moneybox_id === $data->to_moneybox_id,
            InvalidArgumentException::class,
            'Cannot transfer to the same moneybox.'
        );

        throw_if(
            $data->amount <= 0,
            InvalidArgumentException::class,
            'Transfer amount must be positive.'
        );

        return DB::transaction(function () use ($data): array {
            // Record outgoing transaction from source moneybox
            $fromTransaction = $this->recordMoneyboxTransaction->handle(
                new RecordMoneyboxTransactionData(
                    moneybox_id: $data->from_moneybox_id,
                    type: MoneyboxTransactionTypeEnum::TRANSFER,
                    amount: $data->amount,
                    reference: $data->reference,
                    notes: $data->notes ?? 'Transfer out',
                    payment_id: null,
                    expense_id: null,
                    transfer_to_moneybox_id: $data->to_moneybox_id,
                    created_by: $data->created_by,
                )
            );

            // Record incoming transaction to destination moneybox
            $toTransaction = $this->recordMoneyboxTransaction->handle(
                new RecordMoneyboxTransactionData(
                    moneybox_id: $data->to_moneybox_id,
                    type: MoneyboxTransactionTypeEnum::IN,
                    amount: $data->amount,
                    reference: $data->reference,
                    notes: $data->notes ?? 'Transfer in',
                    payment_id: null,
                    expense_id: null,
                    transfer_to_moneybox_id: $data->from_moneybox_id,
                    created_by: $data->created_by,
                )
            );

            return [
                'from' => $fromTransaction,
                'to' => $toTransaction,
            ];
        });
    }
}
