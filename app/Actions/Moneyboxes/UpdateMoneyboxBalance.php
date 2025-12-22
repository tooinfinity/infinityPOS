<?php

declare(strict_types=1);

namespace App\Actions\Moneyboxes;

use App\Models\Moneybox;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class UpdateMoneyboxBalance
{
    /**
     * Directly update moneybox balance (for manual corrections/reconciliation).
     * Use RecordMoneyboxTransaction for normal operations.
     *
     * @throws Throwable
     */
    public function handle(Moneybox $moneybox, int $newBalance, int $userId): Moneybox
    {
        return DB::transaction(function () use ($moneybox, $newBalance, $userId): Moneybox {
            $moneybox->update([
                'balance' => $newBalance,
                'updated_by' => $userId,
            ]);

            return $moneybox;
        });
    }
}
