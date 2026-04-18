<?php

declare(strict_types=1);

namespace App\Actions\SaleReturn;

use App\Actions\Payment\RecordPayment;
use App\Actions\Stock\AddStock;
use App\Data\Payment\PaymentData;
use App\Enums\PaymentStatusEnum;
use App\Enums\ReturnStatusEnum;
use App\Exceptions\InvalidBatchException;
use App\Exceptions\StateTransitionException;
use App\Models\Batch;
use App\Models\SaleReturn;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CompleteSaleReturn
{
    public function __construct(
        private AddStock $addStock,
        private RecordPayment $recordPayment,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(SaleReturn $return, bool $autoRefund = true): SaleReturn
    {
        /** @var SaleReturn $result */
        $result = DB::transaction(function () use ($return, $autoRefund): SaleReturn {
            if (! $return->status->canTransitionTo(ReturnStatusEnum::Completed)) {
                throw new StateTransitionException($return->status->value, ReturnStatusEnum::Completed->value);
            }

            $return->load('items.batch');

            foreach ($return->items as $item) {
                if (! $item->batch instanceof Batch) {
                    /** @var int $batchId */
                    $batchId = $item->batch_id ?? null;
                    throw new InvalidBatchException(
                        $batchId,
                        "Batch not found for product #$item->product_id in return $return->reference_no."
                    );
                }

                $this->addStock->handle(
                    batch: $item->batch,
                    quantity: $item->quantity,
                    reference: $return,
                    note: "Sale return received: $return->reference_no",
                );
            }

            $return->forceFill([
                'status' => ReturnStatusEnum::Completed,
            ])->save();

            if ($autoRefund && $return->total_amount > 0) {
                $this->createAutoRefund($return);
            }

            return $return->refresh()->load('items');
        });

        return $result;
    }

    /**
     * @throws Throwable
     */
    private function createAutoRefund(SaleReturn $return): void
    {
        $paymentMethod = $return->sale->payments()
            ->where('status', '!=', 'voided')
            ->orderByDesc('id')
            ->first()?->payment_method_id;

        if ($paymentMethod === null) {
            return;
        }

        $paymentData = new PaymentData(
            payment_method_id: $paymentMethod,
            amount: -$return->total_amount,
            payment_date: now()->toDateString(),
            note: "Auto-refund for return: {$return->reference_no}",
        );

        $this->recordPayment->handle($return, $paymentData);

        $return->forceFill([
            'paid_amount' => $return->total_amount,
            'payment_status' => PaymentStatusEnum::Paid,
        ])->save();
    }
}
