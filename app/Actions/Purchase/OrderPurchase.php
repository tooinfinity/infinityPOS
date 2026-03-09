<?php

declare(strict_types=1);

namespace App\Actions\Purchase;

use App\Enums\PurchaseStatusEnum;
use App\Exceptions\StateTransitionException;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class OrderPurchase
{
    /**
     * @throws Throwable
     */
    public function handle(Purchase $purchase): Purchase
    {
        /** @var Purchase $result */
        $result = DB::transaction(static function () use ($purchase): Purchase {
            if (! $purchase->status->canTransitionTo(PurchaseStatusEnum::Ordered)) {
                throw new StateTransitionException($purchase->status->value, PurchaseStatusEnum::Ordered->value);
            }

            $purchase->forceFill([
                'status' => PurchaseStatusEnum::Ordered,
            ])->save();

            return $purchase->refresh();
        });

        return $result;
    }
}
