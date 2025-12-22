<?php

declare(strict_types=1);

namespace App\Actions\Payments;

use App\Data\Payments\RecordMoneyboxTransactionData;
use App\Models\Moneybox;
use App\Models\MoneyboxTransaction;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class RecordMoneyboxTransaction
{
    /**
     * Record a transaction in a moneybox and update balance.
     *
     * @throws Throwable
     */
    public function handle(RecordMoneyboxTransactionData $data): MoneyboxTransaction
    {
        return DB::transaction(function () use ($data): MoneyboxTransaction {
            $moneybox = Moneybox::query()->lockForUpdate()->findOrFail($data->moneybox_id);

            // Calculate new balance
            $balanceChange = match ($data->type) {
                \App\Enums\MoneyboxTransactionTypeEnum::IN => $data->amount,
                \App\Enums\MoneyboxTransactionTypeEnum::OUT => -$data->amount,
                \App\Enums\MoneyboxTransactionTypeEnum::TRANSFER => -$data->amount,
            };

            $newBalance = $moneybox->balance + $balanceChange;

            // Create transaction record
            $transaction = MoneyboxTransaction::query()->create([
                'moneybox_id' => $data->moneybox_id,
                'type' => $data->type,
                'amount' => $data->amount,
                'balance_after' => $newBalance,
                'reference' => $data->reference,
                'notes' => $data->notes,
                'payment_id' => $data->payment_id,
                'expense_id' => $data->expense_id,
                'transfer_to_moneybox_id' => $data->transfer_to_moneybox_id,
                'created_by' => $data->created_by,
            ]);

            // Update moneybox balance
            $moneybox->update(['balance' => $newBalance]);

            return $transaction;
        });
    }
}
