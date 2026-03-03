<?php

declare(strict_types=1);

namespace App\Actions\Purchase;

use App\Actions\Shared\ValidateStatusIsPending;
use App\Enums\PurchaseStatusEnum;
use App\Exceptions\InvalidOperationException;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class MarkPurchaseAsOrdered
{
    public function __construct(private ValidateStatusIsPending $validateStatus) {}

    /**
     * @throws Throwable
     */
    public function handle(Purchase $purchase): Purchase
    {
        return DB::transaction(function () use ($purchase): Purchase {
            /** @var Purchase $purchase */
            $purchase = Purchase::query()
                ->lockForUpdate()
                ->findOrFail($purchase->id);

            $this->validateStatus->validateTransition(
                $purchase->status,
                PurchaseStatusEnum::Ordered,
                'Purchase'
            );

            throw_if(! $purchase->items()->exists(), InvalidOperationException::class, 'order', 'Purchase', 'Cannot order a purchase with no items.');

            $purchase->forceFill(['status' => PurchaseStatusEnum::Ordered])->save();

            return $purchase->refresh();
        });
    }
}
