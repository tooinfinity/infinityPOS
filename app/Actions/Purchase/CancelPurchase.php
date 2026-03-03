<?php

declare(strict_types=1);

namespace App\Actions\Purchase;

use App\Enums\PurchaseStatusEnum;
use App\Exceptions\StateTransitionException;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

final readonly class CancelPurchase
{
    /**
     * @throws Throwable
     */
    public function handle(Purchase $purchase): Purchase
    {
        $purchase->refresh();

        $documentPath = $purchase->document;

        $cancelledPurchase = DB::transaction(function () use ($purchase): Purchase {
            /** @var Purchase $purchase */
            $purchase = Purchase::query()
                ->lockForUpdate()
                ->findOrFail($purchase->id);

            throw_if(
                ! $purchase->status->canTransitionTo(PurchaseStatusEnum::Cancelled),
                StateTransitionException::class,
                $purchase->status->label(),
                PurchaseStatusEnum::Cancelled->label()
            );

            $purchase->forceFill([
                'status' => PurchaseStatusEnum::Cancelled,
                'document' => null,
            ])->save();

            return $purchase->refresh();
        });

        if ($documentPath !== null) {
            Storage::disk('public')->delete($documentPath);
        }

        return $cancelledPurchase;
    }
}
