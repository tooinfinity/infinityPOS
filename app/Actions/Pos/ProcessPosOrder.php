<?php

declare(strict_types=1);

namespace App\Actions\Pos;

use App\Actions\GenerateReferenceNo;
use App\Actions\Payment\RecordPayment;
use App\Actions\Stock\DeductStock;
use App\Data\Payment\PaymentData;
use App\Data\Pos\PosCartItemData;
use App\Data\Pos\PosOrderData;
use App\Enums\PaymentStatusEnum;
use App\Enums\SaleStatusEnum;
use App\Exceptions\InvalidOperationException;
use App\Models\Batch;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class ProcessPosOrder
{
    public function __construct(
        private GenerateReferenceNo $referenceGenerator,
        private DeductStock $deductStock,
        private RecordPayment $recordPayment,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(PosOrderData $data): PosOrderResult
    {
        if ($data->cash_tendered < $data->total_amount) {
            throw new InvalidOperationException(
                'process',
                'PosOrder',
                sprintf(
                    'Cash tendered (%d) is less than total amount (%d).',
                    $data->cash_tendered,
                    $data->total_amount,
                )
            );
        }

        /** @var PosOrderResult $result */
        $result = DB::transaction(function () use ($data): PosOrderResult {

            $sale = Sale::query()->forceCreate([
                'customer_id' => $data->customer_id,
                'warehouse_id' => $data->warehouse_id,
                'user_id' => auth()->id(),
                'reference_no' => $this->referenceGenerator->handle('POS', Sale::class),
                'status' => SaleStatusEnum::Completed,
                'sale_date' => now(),
                'total_amount' => $data->total_amount,
                'paid_amount' => 0,
                'change_amount' => 0,
                'payment_status' => PaymentStatusEnum::Unpaid,
                'note' => $data->note,
            ]);

            $data->items->toCollection()
                ->each(function (PosCartItemData $itemData) use ($sale): void {
                    $sale->items()->forceCreate([
                        'product_id' => $itemData->product_id,
                        'batch_id' => $itemData->batch_id,
                        'quantity' => $itemData->quantity,
                        'unit_price' => $itemData->unit_price,
                        'unit_cost' => $itemData->unit_cost,
                        'subtotal' => $itemData->unit_price * $itemData->quantity,
                    ]);

                    if ($itemData->batch_id !== null) {
                        $batch = Batch::query()->findOrFail($itemData->batch_id);

                        $this->deductStock->handle(
                            batch: $batch,
                            quantity: $itemData->quantity,
                            reference: $sale,
                            note: "POS sale: $sale->reference_no",
                        );
                    }
                });

            $payment = $this->recordPayment->handle(
                payable: $sale,
                data: PaymentData::from([
                    'payment_method_id' => $data->payment_method_id,
                    'amount' => $data->total_amount,
                    'payment_date' => now()->toDateString(),
                    'note' => 'POS cash payment',
                ]),
            );

            // 4. UpdatePaymentStatus already called inside RecordPayment —
            //    but we need the refreshed sale to read change_amount correctly
            $sale->refresh();

            $changeAmount = max(0, $data->cash_tendered - $data->total_amount);

            return new PosOrderResult(
                sale: $sale->load([
                    'items.product.unit',
                    'items.batch',
                    'customer',
                    'warehouse',
                    'payments.paymentMethod',
                ]),
                payment: $payment,
                changeAmount: $changeAmount,
            );
        });

        return $result;
    }
}
