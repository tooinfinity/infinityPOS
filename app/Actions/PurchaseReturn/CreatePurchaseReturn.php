<?php

declare(strict_types=1);

namespace App\Actions\PurchaseReturn;

use App\Actions\GenerateReferenceNo;
use App\Data\PurchaseReturn\PurchaseReturnData;
use App\Data\PurchaseReturn\PurchaseReturnItemData;
use App\Enums\PaymentStatusEnum;
use App\Enums\PurchaseStatusEnum;
use App\Enums\ReturnStatusEnum;
use App\Exceptions\InvalidOperationException;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreatePurchaseReturn
{
    public function __construct(
        private GenerateReferenceNo $referenceGenerator,
        private ResolveReturnableQuantity $resolveReturnableQuantity,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(PurchaseReturnData $data): PurchaseReturn
    {
        /** @var PurchaseReturn $return */
        $return = DB::transaction(function () use ($data): PurchaseReturn {
            $purchase = Purchase::query()->findOrFail($data->purchase_id);

            if ($purchase->status !== PurchaseStatusEnum::Received) {
                throw new InvalidOperationException(
                    'create',
                    'PurchaseReturn',
                    "Returns can only be created for received purchases. Purchase status: {$purchase->status->label()}."
                );
            }

            $returnableMap = $this->resolveReturnableQuantity->handle($purchase);

            $this->resolveReturnableQuantity->validate(
                $returnableMap,
                $data->items,
            );

            $totalAmount = $data->items
                ->toCollection()
                ->sum(fn (PurchaseReturnItemData $item) => $item->unit_cost * $item->quantity);

            $return = PurchaseReturn::query()->forceCreate([
                'purchase_id' => $data->purchase_id,
                'warehouse_id' => $data->warehouse_id,
                'user_id' => auth()->id(),
                'reference_no' => $this->referenceGenerator->handle('PRN', PurchaseReturn::class),
                'status' => ReturnStatusEnum::Pending,
                'return_date' => $data->return_date,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'payment_status' => PaymentStatusEnum::Unpaid,
                'note' => $data->note,
            ]);

            $data->items->toCollection()
                ->each(function (PurchaseReturnItemData $itemData) use ($return): void {
                    $return->items()->forceCreate([
                        'product_id' => $itemData->product_id,
                        'batch_id' => $itemData->batch_id,
                        'quantity' => $itemData->quantity,
                        'unit_cost' => $itemData->unit_cost,
                        'subtotal' => $itemData->unit_cost * $itemData->quantity,
                    ]);
                });

            return $return->load(['items.product', 'items.batch', 'purchase']);
        });

        return $return;
    }
}
