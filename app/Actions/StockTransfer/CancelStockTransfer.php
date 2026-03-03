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
    public function handle(StockTransfer $transfer): bool
    {
        return DB::transaction(static function () use ($transfer): bool {
            /** @var StockTransfer $transfer */
            $transfer = StockTransfer::query()
                ->lockForUpdate()
                ->findOrFail($transfer->id);

            throw_if(
                ! $transfer->status->canTransitionTo(StockTransferStatusEnum::Cancelled),
                StateTransitionException::class,
                $transfer->status->label(),
                StockTransferStatusEnum::Cancelled->label()
            );

            return $transfer->forceFill(['status' => StockTransferStatusEnum::Cancelled])->save();
        });
    }
}
