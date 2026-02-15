<?php

declare(strict_types=1);

namespace App\Actions\StockTransfer;

use App\Enums\StockTransferStatusEnum;
use App\Models\StockTransfer;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class UpdateStockTransfer
{
    /**
     * @param  array{note?: string|null, transfer_date?: DateTimeInterface|string, user_id?: int|null}  $data
     *
     * @throws Throwable
     */
    public function handle(StockTransfer $transfer, array $data): void
    {
        DB::transaction(function () use ($transfer, $data): void {
            throw_if($transfer->status !== StockTransferStatusEnum::Pending, RuntimeException::class, 'Only pending transfers can be updated.');

            $transfer->forceFill($data)->save();
        });
    }
}
