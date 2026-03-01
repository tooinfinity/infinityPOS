<?php

declare(strict_types=1);

namespace App\Actions\SaleReturn;

use App\Enums\ReturnStatusEnum;
use App\Exceptions\RefundNotAllowedException;
use App\Exceptions\StateTransitionException;
use App\Models\SaleReturn;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class DeleteSaleReturn
{
    /**
     * @throws Throwable
     */
    public function handle(SaleReturn $saleReturn): void
    {
        DB::transaction(function () use ($saleReturn): void {
            $this->validateSaleReturnCanBeDeleted($saleReturn);

            $saleReturn->items()->delete();
            $saleReturn->delete();
        });
    }

    /**
     * @throws Throwable
     */
    private function validateSaleReturnCanBeDeleted(SaleReturn $saleReturn): void
    {
        if ($saleReturn->status !== ReturnStatusEnum::Pending) {
            throw new StateTransitionException(
                $saleReturn->status->value,
                'Pending'
            );
        }

        $hasRefunds = $saleReturn->payments()
            ->where('amount', '<', 0)
            ->exists();

        if ($hasRefunds) {
            throw new RefundNotAllowedException(
                'sale return',
                'Cannot delete a sale return that has existing refunds. Please void the refunds first.'
            );
        }
    }
}
