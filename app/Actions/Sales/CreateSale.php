<?php

declare(strict_types=1);

namespace App\Actions\Sales;

use App\Data\Sales\CreateSaleData;
use App\Enums\SaleStatusEnum;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateSale
{
    public function __construct(
        private CreateSaleItem $createSaleItem,
        private CalculateSaleTotals $calculateSaleTotals,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(CreateSaleData $data): Sale
    {
        return DB::transaction(function () use ($data) {
            $sale = Sale::query()->create([
                'reference' => $data->reference,
                'client_id' => $data->client_id,
                'store_id' => $data->store_id,
                'subtotal' => $data->subtotal,
                'discount' => $data->discount,
                'tax' => $data->tax,
                'total' => $data->total,
                'paid' => 0,
                'status' => SaleStatusEnum::PENDING,
                'notes' => $data->notes,
                'created_by' => $data->created_by,
            ]);

            foreach ($data->items as $itemData) {
                $this->createSaleItem->handle($sale, $itemData);
            }

            $this->calculateSaleTotals->handle($sale);

            return $sale;
        });
    }
}
