<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Actions\Shared\CalculatePaymentStatus;
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
        private CalculatePaymentStatus $calculatePaymentStatus,
        private ValidateSaleCompletion $validateSaleCompletion,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(Sale $sale, ?CompleteSaleData $data = null): Sale
    {
        return DB::transaction(function () use ($sale, $data): Sale {
            /** @var Sale $sale */
            $sale = Sale::query()
                ->lockForUpdate()
                ->with('items')
                ->findOrFail($sale->id);

            $this->validateSaleCompletion->handle($sale);

            $this->deductSaleStock->handle($sale, 'Sale completed - stock out', validateAvailability: true);

            $paymentCalculation = $this->calculatePaymentStatus->handle($sale->total_amount, $sale->paid_amount);

            $note = $data instanceof CompleteSaleData ? ($data->note ?? $sale->note) : $sale->note;

            $sale->forceFill([
                'status' => SaleStatusEnum::Completed,
                'payment_status' => $paymentCalculation->paymentStatus,
                'note' => $note,
            ])->save();

            return $sale->refresh();
        });
    }
}
