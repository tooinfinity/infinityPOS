<?php

declare(strict_types=1);

namespace App\Actions\StockMovement;

use App\Enums\StockMovementTypeEnum;
use App\Models\StockMovement;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class RecordStockMovement
{
    /**
     * @param  array{warehouse_id: int, product_id: int, type: StockMovementTypeEnum, quantity: int, previous_quantity: int, current_quantity: int, reference_type: string, reference_id: int, batch_id?: int|null, user_id?: int|null, note?: string|null, created_at?: DateTimeInterface|string|null}  $data
     *
     * @throws Throwable
     */
    public function handle(array $data): StockMovement
    {
        return DB::transaction(static function () use ($data): StockMovement {
            $data['created_at'] ??= now();

            return StockMovement::query()->create($data);
        });
    }
}
