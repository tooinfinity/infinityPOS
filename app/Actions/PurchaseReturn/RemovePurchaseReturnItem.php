<?php

declare(strict_types=1);

namespace App\Actions\PurchaseReturn;

use App\Actions\Shared\RecalculateParentTotal;
use App\Actions\Shared\ValidateStatusIsPending;
use App\Models\PurchaseReturnItem;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class RemovePurchaseReturnItem
{
    public function __construct(
        private ValidateStatusIsPending $validateStatus,
        private RecalculateParentTotal $recalculateTotal,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(PurchaseReturnItem $item): bool
    {
        return DB::transaction(function () use ($item): bool {
            $purchaseReturn = $item->purchaseReturn;

            $this->validateStatus->handle($purchaseReturn, 'Cannot remove items from a non-pending purchase return.');

            $deleted = $item->delete();

            if ($deleted) {
                $this->recalculateTotal->handle($purchaseReturn);
            }

            return (bool) $deleted;
        });
    }
}
