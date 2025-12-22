<?php

declare(strict_types=1);

namespace App\Actions\Moneyboxes;

use App\Models\Moneybox;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

final readonly class DeleteMoneybox
{
    /**
     * Delete a moneybox.
     *
     * @throws Throwable
     */
    public function handle(Moneybox $moneybox): bool
    {
        throw_if(
            $moneybox->balance !== 0,
            InvalidArgumentException::class,
            'Cannot delete moneybox with non-zero balance. Current balance: '.$moneybox->balance
        );

        throw_if(
            $moneybox->transactions()->exists(),
            InvalidArgumentException::class,
            'Cannot delete moneybox with existing transactions. Please archive it instead.'
        );

        return DB::transaction(fn (): bool => (bool) $moneybox->delete());
    }
}
