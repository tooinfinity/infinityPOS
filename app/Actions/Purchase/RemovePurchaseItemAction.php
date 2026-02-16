<?php

declare(strict_types=1);

namespace App\Actions\Purchase;

use App\Enums\PurchaseStatusEnum;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

final readonly class RemovePurchaseItemAction
{
    public function __construct(private ?bool $deleteIfEmpty = true) {}

    /**
     * @throws Throwable
     */
    public function handle(PurchaseItem $item): ?Purchase
    {
        return DB::transaction(function () use ($item): ?Purchase {
            throw_if(
                $item->purchase->status !== PurchaseStatusEnum::Pending,
                RuntimeException::class,
                'Items can only be removed from pending purchases.'
            );

            $purchase = $item->purchase;

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

            $this->recalculatePurchaseTotal($purchase);

            return $purchase->refresh();
        });
    }

    private function recalculatePurchaseTotal(Purchase $purchase): void
    {
        $total = PurchaseItem::query()
            ->where('purchase_id', $purchase->id)
            ->sum('subtotal');

        $purchase->forceFill(['total_amount' => $total])->save();
    }
}
