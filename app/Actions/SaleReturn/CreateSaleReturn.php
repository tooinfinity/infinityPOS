<?php

declare(strict_types=1);

namespace App\Actions\SaleReturn;

use App\Actions\GenerateReferenceNo;
use App\Data\SaleReturn\SaleReturnData;
use App\Data\SaleReturn\SaleReturnItemData;
use App\Enums\PaymentStatusEnum;
use App\Enums\ReturnStatusEnum;
use App\Enums\SaleStatusEnum;
use App\Exceptions\InvalidOperationException;
use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateSaleReturn
{
    public function __construct(
        private GenerateReferenceNo $referenceGenerator,
        private ResolveReturnableQuantity $resolveReturnableQuantity,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(SaleReturnData $data): SaleReturn
    {
        /** @var SaleReturn $return */
        $return = DB::transaction(function () use ($data): SaleReturn {
            $sale = Sale::query()->findOrFail($data->sale_id);

            if ($sale->status !== SaleStatusEnum::Completed) {
                throw new InvalidOperationException(
                    'create',
                    'SaleReturn',
                    "Returns can only be created for completed sales. Sale status: {$sale->status->label()}."
                );
            }

            $returnableMap = $this->resolveReturnableQuantity->handle($sale);

            $this->resolveReturnableQuantity->validate(
                $returnableMap,
                $data->items,
            );

            $totalAmount = $data->items
                ->toCollection()
                ->sum(fn (SaleReturnItemData $item) => $item->unit_price * $item->quantity);

            $return = SaleReturn::query()->forceCreate([
                'sale_id' => $data->sale_id,
                'warehouse_id' => $data->warehouse_id,
                'user_id' => auth()->id(),
                'reference_no' => $this->referenceGenerator->handle('SRN', SaleReturn::class),
                'status' => ReturnStatusEnum::Pending,
                'return_date' => $data->return_date,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'payment_status' => PaymentStatusEnum::Unpaid,
                'note' => $data->note,
            ]);

            $data->items->toCollection()
                ->each(function (SaleReturnItemData $itemData) use ($return): void {
                    $return->items()->forceCreate([
                        'product_id' => $itemData->product_id,
                        'batch_id' => $itemData->batch_id,
                        'quantity' => $itemData->quantity,
                        'unit_price' => $itemData->unit_price,
                        'subtotal' => $itemData->unit_price * $itemData->quantity,
                    ]);
                });

            return $return->load(['items.product', 'items.batch', 'sale']);
        });

        return $return;
    }
}
