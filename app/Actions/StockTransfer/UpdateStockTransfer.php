<?php

declare(strict_types=1);

namespace App\Actions\StockTransfer;

use App\Data\StockTransfer\UpdateStockTransferData;
use App\Enums\StockTransferStatusEnum;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Spatie\LaravelData\Optional;
use Throwable;

final readonly class UpdateStockTransfer
{
    /**
     * @throws Throwable
     */
    public function handle(StockTransfer $transfer, UpdateStockTransferData $data): StockTransfer
    {
        return DB::transaction(static function () use ($transfer, $data): StockTransfer {
            throw_if($transfer->status !== StockTransferStatusEnum::Pending, RuntimeException::class, 'Only pending transfers can be updated.');

            $updateData = [];

            if (! $data->note instanceof Optional) {
                $updateData['note'] = $data->note;
            }
            if (! $data->transfer_date instanceof Optional) {
                $updateData['transfer_date'] = $data->transfer_date;
            }
            if (! $data->user_id instanceof Optional) {
                $updateData['user_id'] = $data->user_id;
            }

            $transfer->update($updateData);

            return $transfer->refresh();
        });
    }
}
