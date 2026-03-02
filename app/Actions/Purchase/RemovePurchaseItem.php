<?php

declare(strict_types=1);

namespace App\Actions\Purchase;

use App\Actions\Shared\RecalculateParentTotal;
use App\Actions\Shared\ValidateStatusIsPending;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

final readonly class RemovePurchaseItem
{
    public function __construct(
        private ValidateStatusIsPending $validateStatus,
        private RecalculateParentTotal $recalculateTotal,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(PurchaseItem $item, bool $deleteIfEmpty = true): ?Purchase
    {
        return DB::transaction(function () use ($item, $deleteIfEmpty): ?Purchase {
            /** @var Purchase $purchase */
            $purchase = Purchase::query()
                ->lockForUpdate()
                ->findOrFail($item->purchase_id);

            $this->validateStatus->handle($purchase);

            $item->delete();

            $remainingItems = PurchaseItem::query()
                ->where('purchase_id', $purchase->id)
                ->count();

            if ($remainingItems === 0 && $deleteIfEmpty) {
                if ($purchase->document !== null) {
                    Storage::disk('public')->delete($purchase->document);
                }
                $purchase->delete();

                return null;
            }

            $this->recalculateTotal->handle($purchase);

            return $purchase->refresh();
        });
    }
}
