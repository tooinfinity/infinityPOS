<?php

declare(strict_types=1);

namespace App\Actions\Shared;

use App\Enums\HasStatusTransitions;
use App\Enums\PurchaseStatusEnum;
use App\Enums\ReturnStatusEnum;
use App\Enums\SaleStatusEnum;
use App\Enums\StockTransferStatusEnum;
use App\Exceptions\StateTransitionException;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;

final readonly class ValidateStatusIsPending
{
    /**
     * @throws StateTransitionException
     */
    public function handle(Sale|SaleReturn|Purchase|PurchaseReturn|StockTransfer $model): void
    {
        $pendingStatus = $this->getPendingStatus($model);

        if ($model->status !== $pendingStatus) {
            throw new StateTransitionException(
                (string) $model->status->value,
                (string) $pendingStatus->value
            );
        }
    }

    /**
     * @throws StateTransitionException
     */
    public function forItem(StockTransferItem $item): void
    {
        $transfer = $item->stockTransfer;

        if ($transfer->status !== StockTransferStatusEnum::Pending) {
            throw new StateTransitionException(
                $transfer->status->value,
                StockTransferStatusEnum::Pending->value
            );
        }
    }

    /**
     * Validate that a status can transition to another status.
     *
     * @throws StateTransitionException
     */
    public function validateTransition(HasStatusTransitions $currentStatus, HasStatusTransitions $targetStatus, ?string $entityLabel = null): void
    {
        if (! $currentStatus->canTransitionTo($targetStatus)) {
            throw new StateTransitionException(
                $entityLabel ? "{$entityLabel} (".$currentStatus->value.')' : (string) $currentStatus->value,
                (string) $targetStatus->value
            );
        }
    }

    private function getPendingStatus(Sale|SaleReturn|Purchase|PurchaseReturn|StockTransfer $model): HasStatusTransitions
    {
        return match ($model::class) {
            Sale::class => SaleStatusEnum::Pending,
            SaleReturn::class => ReturnStatusEnum::Pending,
            Purchase::class => PurchaseStatusEnum::Pending,
            PurchaseReturn::class => ReturnStatusEnum::Pending,
            StockTransfer::class => StockTransferStatusEnum::Pending,
        };
    }
}
