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
        private ?ValidateStatusIsPending $validateStatus = null,
        private ?RecalculateParentTotal $recalculateTotal = null,
        private ?bool $deleteIfEmpty = true,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(PurchaseItem $item): ?Purchase
    {
        $validateStatus = $this->validateStatus ?? new ValidateStatusIsPending();
        $recalculateTotal = $this->recalculateTotal ?? new RecalculateParentTotal();

        return DB::transaction(function () use ($item, $validateStatus, $recalculateTotal): ?Purchase {
            /** @var Purchase $purchase */
            $purchase = Purchase::query()
                ->lockForUpdate()
                ->findOrFail($item->purchase_id);

            $validateStatus->handle($purchase);

            $item->delete();

            $remainingItems = PurchaseItem::query()
                ->where('purchase_id', $purchase->id)
                ->count();

            if ($remainingItems === 0 && $this->deleteIfEmpty) {
                if ($purchase->document !== null) {
                    Storage::disk('public')->delete($purchase->document);
                }
                $purchase->delete();

                return null;
            }

            $recalculateTotal->handle($purchase);

            return $purchase->refresh();
        });
    }
}
