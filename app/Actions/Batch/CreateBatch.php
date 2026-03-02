<?php

declare(strict_types=1);

namespace App\Actions\Batch;

use App\Actions\StockMovement\RecordStockMovement;
use App\Data\Batch\CreateBatchData;
use App\Data\StockMovement\RecordStockMovementData;
use App\Enums\StockMovementTypeEnum;
use App\Models\Batch;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateBatch
{
    public function __construct(
        private RecordStockMovement $recordStockMovement,
        private BatchNumberGenerator $generator,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(CreateBatchData $data): Batch
    {
        $recordStockMovement = $this->recordStockMovement;
        $generator = $this->generator;

        return DB::transaction(static function () use ($data, $recordStockMovement, $generator): Batch {
            $batch = Batch::query()->forceCreate([
                'product_id' => $data->product_id,
                'warehouse_id' => $data->warehouse_id,
                'batch_number' => $data->batch_number ?? $generator->handle($data->product_id),
                'cost_amount' => $data->cost_amount,
                'quantity' => $data->quantity,
                'expires_at' => $data->expires_at,
            ])->refresh();

            if ($data->quantity > 0) {
                $recordStockMovement->handle(new RecordStockMovementData(
                    warehouse_id: $data->warehouse_id,
                    product_id: $data->product_id,
                    type: StockMovementTypeEnum::In,
                    quantity: $data->quantity,
                    previous_quantity: 0,
                    current_quantity: $data->quantity,
                    reference_type: Batch::class,
                    reference_id: $batch->id,
                    batch_id: $batch->id,
                    user_id: $data->user_id,
                    note: 'Batch created',
                ));
            }

            return $batch;
        });
    }
}
