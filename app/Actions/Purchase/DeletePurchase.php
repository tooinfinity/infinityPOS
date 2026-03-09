<?php

declare(strict_types=1);

namespace App\Actions\Purchase;

use App\Enums\PurchaseStatusEnum;
use App\Exceptions\InvalidOperationException;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class DeletePurchase
{
    /**
     * @throws Throwable
     */
    public function handle(Purchase $purchase): bool
    {
        /** @var bool $result */
        $result = DB::transaction(static function () use ($purchase): bool {
            if (! in_array($purchase->status, [
                PurchaseStatusEnum::Pending,
                PurchaseStatusEnum::Cancelled,
            ], true)) {
                throw new InvalidOperationException(
                    'delete',
                    'Purchase',
                    "Only pending or cancelled purchases can be deleted. Current status: {$purchase->status->label()}."
                );
            }

            throw_if($purchase->payments()->active()->exists(), InvalidOperationException::class, 'delete', 'Purchase', 'Cannot delete a purchase with active payments.');

            $purchase->items()->delete();

            return (bool) $purchase->delete();
        });

        return $result;
    }
}
