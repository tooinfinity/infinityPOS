<?php

declare(strict_types=1);

namespace App\Actions\Purchase;

use App\Actions\Stock\DeductStock;
use App\Enums\PurchaseStatusEnum;
use App\Exceptions\InvalidOperationException;
use App\Exceptions\StateTransitionException;
use App\Models\Batch;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CancelPurchase
{
    public function __construct(
        private DeductStock $deductStock,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(Purchase $purchase, ?string $reason = null): Purchase
    {
        /** @var Purchase $result */
        $result = DB::transaction(function () use ($purchase, $reason): Purchase {
            if (! $purchase->status->canTransitionTo(PurchaseStatusEnum::Cancelled)) {
                throw new StateTransitionException($purchase->status->value, PurchaseStatusEnum::Cancelled->value);
            }

            if ($purchase->payments()->active()->exists()) {
                throw new InvalidOperationException(
                    'cancel',
                    'Purchase',
                    'Cannot cancel a purchase with active payments. Void payments first.'
                );
            }

            if ($purchase->status === PurchaseStatusEnum::Received) {
                $purchase->load('items.batch');

                $purchase->items
                    ->filter(fn ($item) => $item->received_quantity > 0)
                    ->each(function ($item) use ($purchase, $reason): void {
                        if ($item->batch instanceof Batch) {
                            $this->deductStock->handle(
                                batch: $item->batch,
                                quantity: $item->received_quantity,
                                reference: $purchase,
                                note: $reason ?? "Purchase cancelled: $purchase->reference_no",
                            );
                        }
                    });
            }

            $purchase->forceFill([
                'status' => PurchaseStatusEnum::Cancelled,
            ])->save();

            return $purchase->refresh();
        });

        return $result;
    }
}
