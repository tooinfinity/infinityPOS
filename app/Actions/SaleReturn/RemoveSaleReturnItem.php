<?php

declare(strict_types=1);

namespace App\Actions\SaleReturn;

use App\Actions\Shared\RecalculateParentTotal;
use App\Actions\Shared\ValidateStatusIsPending;
use App\Models\SaleReturnItem;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class RemoveSaleReturnItem
{
    public function __construct(
        private ValidateStatusIsPending $validateStatus,
        private RecalculateParentTotal $recalculateTotal,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(SaleReturnItem $item): bool
    {
        return DB::transaction(function () use ($item): bool {
            $saleReturn = $item->saleReturn;

            $this->validateStatus->handle($saleReturn, 'Cannot remove items from a non-pending sale return.');

            $deleted = $item->delete();

            if ($deleted) {
                $this->recalculateTotal->handle($saleReturn);
            }

            return (bool) $deleted;
        });
    }
}
