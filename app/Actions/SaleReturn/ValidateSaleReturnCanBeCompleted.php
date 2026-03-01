<?php

declare(strict_types=1);

namespace App\Actions\SaleReturn;

use App\Enums\ReturnStatusEnum;
use App\Exceptions\InvalidOperationException;
use App\Exceptions\StateTransitionException;
use App\Models\SaleReturn;
use Throwable;

final readonly class ValidateSaleReturnCanBeCompleted
{
    /**
     * @throws Throwable
     */
    public function handle(SaleReturn $saleReturn): void
    {
        if ($saleReturn->status !== ReturnStatusEnum::Pending) {
            throw new StateTransitionException(
                $saleReturn->status->value,
                'Completed'
            );
        }

        throw_if($saleReturn->items()->count() === 0, InvalidOperationException::class, 'complete', 'SaleReturn', 'Sale return cannot be completed without items');
    }
}
