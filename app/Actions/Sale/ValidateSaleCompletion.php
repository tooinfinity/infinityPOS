<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Enums\SaleStatusEnum;
use App\Exceptions\InvalidOperationException;
use App\Exceptions\StateTransitionException;
use App\Models\Sale;
use Throwable;

final readonly class ValidateSaleCompletion
{
    /**
     * @throws Throwable
     */
    public function handle(Sale $sale): void
    {
        if (! $sale->status->canTransitionTo(SaleStatusEnum::Completed)) {
            throw new StateTransitionException(
                $sale->status->value,
                'Completed'
            );
        }

        throw_if($sale->items->isEmpty(), InvalidOperationException::class, 'complete', 'Sale', 'Sale cannot be completed without items');
    }
}
