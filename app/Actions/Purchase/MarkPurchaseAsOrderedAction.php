<?php

declare(strict_types=1);

namespace App\Actions\Purchase;

use App\Enums\PurchaseStatusEnum;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class MarkPurchaseAsOrderedAction
{
    /**
     * @throws Throwable
     */
    public function handle(Purchase $purchase): Purchase
    {
        return DB::transaction(static function () use ($purchase): Purchase {
            throw_if(
                $purchase->status !== PurchaseStatusEnum::Pending,
                RuntimeException::class,
                'Only pending purchases can be marked as ordered.'
            );

            throw_if(
                $purchase->items()->count() === 0,
                RuntimeException::class,
                'Cannot order a purchase with no items.'
            );

            $purchase->forceFill(['status' => PurchaseStatusEnum::Ordered])->save();

            return $purchase->refresh();
        });
    }
}
