<?php

declare(strict_types=1);

namespace App\Actions\PurchaseReturn;

use App\Actions\Shared\RecalculateParentTotal;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class RemovePurchaseReturnItem
{
    public function __construct(
        private RecalculateParentTotal $recalculateTotal,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(PurchaseReturnItem $item): bool
    {
        return DB::transaction(function () use ($item): bool {
            /** @var PurchaseReturn $purchaseReturn */
            $purchaseReturn = PurchaseReturn::query()
                ->lockForUpdate()
                ->findOrFail($item->purchase_return_id);

            $deleted = $item->delete();

            if ($deleted) {
                $this->recalculateTotal->handle($purchaseReturn);
            }

            return (bool) $deleted;
        });
    }
}
