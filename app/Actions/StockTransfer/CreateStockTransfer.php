<?php

declare(strict_types=1);

namespace App\Actions\StockTransfer;

use App\Enums\StockTransferStatusEnum;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final readonly class CreateStockTransfer
{
    /**
     * @param  array{from_warehouse_id: int, to_warehouse_id: int, note?: string|null, transfer_date?: DateTimeInterface|string, user_id?: int|null, items: array<int, array{product_id: int, batch_id?: int|null, quantity: int}>}  $data
     *
     * @throws Throwable
     */
    public function handle(array $data): StockTransfer
    {
        return DB::transaction(function () use ($data): StockTransfer {
            throw_if($data['from_warehouse_id'] === $data['to_warehouse_id'], RuntimeException::class, 'Source and destination warehouse cannot be the same.');

            $transfer = new StockTransfer();
            $transfer->forceFill([
                'from_warehouse_id' => $data['from_warehouse_id'],
                'to_warehouse_id' => $data['to_warehouse_id'],
                'reference_no' => $this->generateReferenceNo(),
                'status' => StockTransferStatusEnum::Pending,
                'note' => $data['note'] ?? null,
                'transfer_date' => $data['transfer_date'] ?? now(),
                'user_id' => $data['user_id'] ?? null,
            ])->save();

            foreach ($data['items'] as $item) {
                $transferItem = new StockTransferItem();
                $transferItem->forceFill([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'batch_id' => $item['batch_id'] ?? null,
                    'quantity' => $item['quantity'],
                ])->save();
            }

            return $transfer;
        });
    }

    private function generateReferenceNo(): string
    {
        return 'STF-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
    }
}
