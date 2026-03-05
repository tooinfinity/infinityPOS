<?php

declare(strict_types=1);

namespace App\Actions\StockTransfer;

use App\Enums\StockTransferStatusEnum;
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

            return $transfer->forceFill(['status' => StockTransferStatusEnum::Cancelled])->save();
        });
    }
}
