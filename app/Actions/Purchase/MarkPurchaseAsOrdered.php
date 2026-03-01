<?php

declare(strict_types=1);

namespace App\Actions\Purchase;

use App\Enums\PurchaseStatusEnum;
use App\Exceptions\InvalidOperationException;
use App\Exceptions\StateTransitionException;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class MarkPurchaseAsOrdered
{
    /**
     * @throws Throwable
     */
    public function handle(Purchase $purchase): Purchase
    {
        return DB::transaction(static function () use ($purchase): Purchase {
            /** @var Purchase $purchase */
            $purchase = Purchase::query()
                ->lockForUpdate()
                ->findOrFail($purchase->id);

            if (! $purchase->status->canTransitionTo(PurchaseStatusEnum::Ordered)) {
                throw new StateTransitionException(
                    $purchase->status->label(),
                    PurchaseStatusEnum::Ordered->label()
                );
            }

            if ($purchase->items()->count() === 0) {
                throw new InvalidOperationException(
                    'order',
                    'Purchase',
                    'Cannot order a purchase with no items.'
                );
            }

            $purchase->forceFill(['status' => PurchaseStatusEnum::Ordered])->save();

            return $purchase->refresh();
        });
    }
}
