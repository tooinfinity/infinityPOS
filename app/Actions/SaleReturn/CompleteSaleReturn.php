<?php

declare(strict_types=1);

namespace App\Actions\SaleReturn;

use App\Actions\Stock\AddStock;
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
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(SaleReturn $return): SaleReturn
    {
        /** @var SaleReturn $result */
        $result = DB::transaction(function () use ($return): SaleReturn {
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

            return $return->refresh();
        });

        return $result;
    }
}
