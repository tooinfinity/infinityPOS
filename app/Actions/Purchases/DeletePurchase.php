<?php

declare(strict_types=1);

namespace App\Actions\Purchases;

use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

final readonly class DeletePurchase
{
    /**
     * @throws Throwable
     */
    public function handle(Purchase $purchase): bool
    {
        throw_if($purchase->status->isCompleted(), InvalidArgumentException::class, 'Cannot delete a received purchase. Please cancel it first.');

        return DB::transaction(function () use ($purchase): bool {
            $purchase->items()->delete();

            $purchase->payments()->delete();

            return (bool) $purchase->delete();
        });
    }
}
