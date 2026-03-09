<?php

declare(strict_types=1);

namespace App\Actions\PurchaseReturn;

use App\Actions\Stock\DeductStock;
use App\Enums\ReturnStatusEnum;
use App\Exceptions\InvalidBatchException;
use App\Exceptions\StateTransitionException;
use App\Models\Batch;
use App\Models\PurchaseReturn;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CompletePurchaseReturn
{
    public function __construct(
        private DeductStock $deductStock,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(PurchaseReturn $return): PurchaseReturn
    {
        /** @var PurchaseReturn $result */
        $result = DB::transaction(function () use ($return): PurchaseReturn {
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

                $this->deductStock->handle(
                    batch: $item->batch,
                    quantity: $item->quantity,
                    reference: $return,
                    note: "Purchase return sent: $return->reference_no",
                );
            }

            $return->forceFill([
                'status' => ReturnStatusEnum::Completed,
            ])->save();

            return $return->refresh();
        });

        return $result;
    }
}
