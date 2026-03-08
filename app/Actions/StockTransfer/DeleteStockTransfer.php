<?php

declare(strict_types=1);

namespace App\Actions\StockTransfer;

use App\Enums\StockTransferStatusEnum;
use App\Exceptions\InvalidOperationException;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class DeleteStockTransfer
{
    /**
     * @throws Throwable
     */
    public function handle(StockTransfer $transfer): bool
    {
        /** @var bool $result */
        $result = DB::transaction(static function () use ($transfer): bool {
            if ($transfer->status !== StockTransferStatusEnum::Pending) {
                throw new InvalidOperationException(
                    'delete',
                    'StockTransfer',
                    "Only pending transfers can be deleted. Current status: {$transfer->status->label()}."
                );
            }

            $transfer->items()->delete();

            return (bool) $transfer->delete();
        });

        return $result;
    }
}
