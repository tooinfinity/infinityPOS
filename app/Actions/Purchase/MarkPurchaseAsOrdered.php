<?php

declare(strict_types=1);

namespace App\Actions\Purchase;

use App\Enums\PurchaseStatusEnum;
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

            $purchase->forceFill(['status' => PurchaseStatusEnum::Ordered])->save();

            return $purchase->refresh();
        });
    }
}
