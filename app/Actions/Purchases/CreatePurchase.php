<?php

declare(strict_types=1);

namespace App\Actions\Purchases;

use App\Data\Purchases\CreatePurchaseData;
use App\Enums\PurchaseStatusEnum;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreatePurchase
{
    public function __construct(
        private CreatePurchaseItem $createPurchaseItem,
        private CalculatePurchaseTotals $calculatePurchaseTotals,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(CreatePurchaseData $data): Purchase
    {
        return DB::transaction(function () use ($data) {
            $purchase = Purchase::query()->create([
                'reference' => $data->reference,
                'supplier_id' => $data->supplier_id,
                'store_id' => $data->store_id,
                'subtotal' => $data->subtotal,
                'discount' => $data->discount,
                'tax' => $data->tax,
                'total' => $data->total,
                'paid' => 0,
                'status' => PurchaseStatusEnum::PENDING,
                'notes' => $data->notes,
                'created_by' => $data->created_by,
            ]);

            foreach ($data->items as $itemData) {
                $this->createPurchaseItem->handle($purchase, $itemData);
            }

            $this->calculatePurchaseTotals->handle($purchase);

            return $purchase;
        });
    }
}
