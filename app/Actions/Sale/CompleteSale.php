<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Actions\Shared\ApplyPaymentSummary;
use App\Actions\Stock\DeductSaleStock;
use App\Data\Sale\CompleteSaleData;
use App\Enums\SaleStatusEnum;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CompleteSale
{
    public function __construct(
        private DeductSaleStock $deductSaleStock,
        private ApplyPaymentSummary $applyPaymentSummary,
        private ValidateSaleCompletion $validateSaleCompletion,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(Sale $sale, ?CompleteSaleData $data = null, bool $skipStockValidation = false): Sale
    {
        return DB::transaction(function () use ($sale, $data, $skipStockValidation): Sale {
            /** @var Sale $sale */
            $sale = Sale::query()
                ->lockForUpdate()
                ->with('items')
                ->findOrFail($sale->id);

            $this->validateSaleCompletion->handle($sale);

            $this->deductSaleStock->handle(
                $sale,
                'Sale completed - stock out',
                validateAvailability: ! $skipStockValidation
            );

            $note = $data instanceof CompleteSaleData ? ($data->note ?? $sale->note) : $sale->note;

            $sale->forceFill([
                'status' => SaleStatusEnum::Completed,
                'note' => $note,
            ])->save();

            $this->applyPaymentSummary->handle($sale, $sale->paid_amount);

            return $sale->refresh();
        });
    }
}
