<?php

declare(strict_types=1);

namespace App\Actions\Shared;

use App\Enums\PurchaseStatusEnum;
use App\Enums\ReturnStatusEnum;
use App\Enums\SaleStatusEnum;
use App\Enums\StockTransferStatusEnum;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use RuntimeException;

final readonly class ValidateStatusIsPending
{
    /**
     * @throws RuntimeException
     */
    public function handle(Sale|SaleReturn|Purchase|PurchaseReturn|StockTransfer $model, ?string $customMessage = null): void
    {
        $pendingStatus = $this->getPendingStatus($model);

        throw_if(
            $model->status !== $pendingStatus,
            RuntimeException::class,
            $customMessage ?? $this->getErrorMessage($model)
        );
    }

    /**
     * @throws RuntimeException
     */
    public function forItem(StockTransferItem $item, ?string $customMessage = null): void
    {
        $transfer = $item->stockTransfer;

        throw_if(
            $transfer->status !== StockTransferStatusEnum::Pending,
            RuntimeException::class,
            $customMessage ?? "Items can only be modified when transfer is pending. Current status: {$transfer->status->value}"
        );
    }

    private function getPendingStatus(Sale|SaleReturn|Purchase|PurchaseReturn|StockTransfer $model): object
    {
        return match ($model::class) {
            Sale::class => SaleStatusEnum::Pending,
            SaleReturn::class => ReturnStatusEnum::Pending,
            Purchase::class => PurchaseStatusEnum::Pending,
            PurchaseReturn::class => ReturnStatusEnum::Pending,
            StockTransfer::class => StockTransferStatusEnum::Pending,
        };
    }

    private function getErrorMessage(Sale|SaleReturn|Purchase|PurchaseReturn|StockTransfer $model): string
    {
        return match ($model::class) {
            Sale::class => "Can only modify items in pending sales. Current status: {$model->status->value}",
            SaleReturn::class => "Cannot add items to a non-pending sale return. Current status: {$model->status->value}",
            Purchase::class => "Items can only be added to pending purchases. Current status: {$model->status->value}",
            PurchaseReturn::class => "Cannot add items to a non-pending purchase return. Current status: {$model->status->value}",
            StockTransfer::class => "Items can only be added to pending transfers. Current status: {$model->status->value}",
        };
    }
}
