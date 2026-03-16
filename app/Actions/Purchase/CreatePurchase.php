<?php

declare(strict_types=1);

namespace App\Actions\Purchase;

use App\Actions\GenerateReferenceNo;
use App\Data\Purchase\PurchaseData;
use App\Data\Purchase\PurchaseItemData;
use App\Enums\PaymentStatusEnum;
use App\Enums\PurchaseStatusEnum;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreatePurchase
{
    public function __construct(
        private GenerateReferenceNo $referenceGenerator,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(PurchaseData $data): Purchase
    {
        /** @var Purchase $purchase */
        $purchase = DB::transaction(function () use ($data): Purchase {
            $purchase = Purchase::query()->forceCreate([
                'supplier_id' => $data->supplier_id,
                'warehouse_id' => $data->warehouse_id,
                'user_id' => auth()->id(),
                'reference_no' => $this->referenceGenerator->handle('PUR'),
                'status' => PurchaseStatusEnum::Pending,
                'purchase_date' => $data->purchase_date,
                'total_amount' => $data->total_amount,
                'paid_amount' => 0,
                'payment_status' => PaymentStatusEnum::Unpaid,
                'note' => $data->note,
            ]);

            $data->items->toCollection()
                ->each(function (PurchaseItemData $itemData) use ($purchase): void {
                    $purchase->items()->forceCreate([
                        'product_id' => $itemData->product_id,
                        'quantity' => $itemData->quantity,
                        'received_quantity' => 0, // nothing received yet
                        'unit_cost' => $itemData->unit_cost,
                        'subtotal' => $itemData->unit_cost * $itemData->quantity,
                        'expires_at' => $itemData->expires_at,
                    ]);
                });

            return $purchase->load(['items.product', 'supplier', 'warehouse']);
        });

        return $purchase;
    }
}
