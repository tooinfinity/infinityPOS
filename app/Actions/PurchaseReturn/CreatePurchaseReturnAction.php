<?php

declare(strict_types=1);

namespace App\Actions\PurchaseReturn;

use App\Data\PurchaseReturn\CreatePurchaseReturnData;
use App\Data\PurchaseReturn\PurchaseReturnItemData;
use App\Enums\PaymentStatusEnum;
use App\Enums\ReturnStatusEnum;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use Illuminate\Support\Str;
use Spatie\LaravelData\DataCollection;
use Throwable;

final readonly class CreatePurchaseReturnAction
{
    /**
     * @throws Throwable
     */
    public function handle(CreatePurchaseReturnData $data): PurchaseReturn
    {
        $totalAmount = $this->calculateTotalAmount($data->items);

        $purchaseReturn = PurchaseReturn::query()->forceCreate([
            'purchase_id' => $data->purchase_id,
            'warehouse_id' => $data->warehouse_id,
            'user_id' => $data->user_id,
            'reference_no' => $this->generateReferenceNo(),
            'return_date' => $data->return_date,
            'total_amount' => $totalAmount,
            'paid_amount' => 0,
            'payment_status' => PaymentStatusEnum::Unpaid,
            'status' => ReturnStatusEnum::Pending,
            'note' => $data->note,
        ]);

        foreach ($data->items as $item) {
            PurchaseReturnItem::query()->forceCreate([
                'purchase_return_id' => $purchaseReturn->id,
                'product_id' => $item->product_id,
                'batch_id' => $item->batch_id,
                'quantity' => $item->quantity,
                'unit_cost' => $item->unit_cost,
                'subtotal' => $item->quantity * $item->unit_cost,
            ]);
        }

        return $purchaseReturn->refresh();
    }

    /**
     * @param  DataCollection<int, PurchaseReturnItemData>  $items
     */
    private function calculateTotalAmount(DataCollection $items): int
    {
        $total = 0;

        foreach ($items as $item) {
            $total += $item->quantity * $item->unit_cost;
        }

        return $total;
    }

    private function generateReferenceNo(): string
    {
        do {
            $reference = 'PRET-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
        } while (PurchaseReturn::query()->where('reference_no', $reference)->exists());

        return $reference;
    }
}
