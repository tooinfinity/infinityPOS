<?php

declare(strict_types=1);

namespace App\Actions\StockTransfer;

use App\Enums\StockTransferStatusEnum;
use App\Models\StockTransferItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class UpdateStockTransferItem
{
    /**
     * @param  array{batch_id?: int|null, quantity?: int}  $data
     *
     * @throws Throwable
     */
    public function handle(StockTransferItem $item, array $data): void
    {
        DB::transaction(function () use ($item, $data): void {
            throw_if($item->stockTransfer?->status !== StockTransferStatusEnum::Pending, RuntimeException::class, 'Items can only be updated when transfer is pending.');

            $item->forceFill($data)->save();
        });
    }
}
