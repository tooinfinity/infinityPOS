<?php

declare(strict_types=1);

namespace App\Actions\StockTransfer;

use App\Enums\StockTransferStatusEnum;
use App\Exceptions\StateTransitionException;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CancelStockTransfer
{
    /**
     * @throws Throwable
     */
    public function handle(StockTransfer $transfer): StockTransfer
    {
        /** @var StockTransfer $result */
        $result = DB::transaction(static function () use ($transfer): StockTransfer {
            if (! $transfer->status->canTransitionTo(StockTransferStatusEnum::Cancelled)) {
                throw new StateTransitionException(
                    $transfer->status->value,
                    StockTransferStatusEnum::Cancelled->value,
                );
            }

            $transfer->forceFill([
                'status' => StockTransferStatusEnum::Cancelled,
            ])->save();

            return $transfer->refresh();
        });

        return $result;
    }
}
