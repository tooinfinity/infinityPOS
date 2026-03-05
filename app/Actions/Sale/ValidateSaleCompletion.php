<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Exceptions\InvalidOperationException;
use App\Models\Sale;
use Throwable;

final readonly class ValidateSaleCompletion
{
    /**
     * @throws Throwable
     */
    public function handle(Sale $sale): void
    {
        throw_if($sale->items->isEmpty(), InvalidOperationException::class, 'complete', 'Sale', 'Sale cannot be completed without items');
    }
}
