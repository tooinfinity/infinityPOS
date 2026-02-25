<?php

declare(strict_types=1);

namespace App\Actions\Purchase;

use App\Enums\PurchaseStatusEnum;
use App\Exceptions\StateTransitionException;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

final readonly class CancelPurchase
{
    /**
     * @throws Throwable
     */
    public function handle(Purchase $purchase): Purchase
    {
        $documentPath = null;

        $cancelledPurchase = DB::transaction(static function () use ($purchase, &$documentPath): Purchase {
            /** @var Purchase $purchase */
            $purchase = Purchase::query()
                ->lockForUpdate()
                ->findOrFail($purchase->id);

            $documentPath = $purchase->document;

            throw_if(
                ! $purchase->status->canTransitionTo(PurchaseStatusEnum::Cancelled),
                StateTransitionException::class,
                $purchase->status->label(),
                PurchaseStatusEnum::Cancelled->label()
            );

            throw_if(
                $purchase->status === PurchaseStatusEnum::Received,
                RuntimeException::class,
                'Received purchases cannot be cancelled.'
            );

            throw_if(
                $purchase->status === PurchaseStatusEnum::Cancelled,
                RuntimeException::class,
                'Purchase is already cancelled.'
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
