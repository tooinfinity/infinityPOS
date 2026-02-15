<?php

declare(strict_types=1);

namespace App\Actions\StockTransfer;

use App\Enums\StockTransferStatusEnum;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class CancelStockTransfer
{
    /**
     * @throws Throwable
     */
    public function handle(StockTransfer $transfer): bool
    {
        return DB::transaction(static function () use ($transfer): bool {
            throw_if($transfer->status !== StockTransferStatusEnum::Pending, RuntimeException::class, 'Only pending transfers can be cancelled.');

            return (bool) $transfer->forceFill(['status' => StockTransferStatusEnum::Cancelled])->save();
        });
    }
}
