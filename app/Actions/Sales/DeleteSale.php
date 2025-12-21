<?php

declare(strict_types=1);

namespace App\Actions\Sales;

use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

final readonly class DeleteSale
{
    /**
     * @throws Throwable
     */
    public function handle(Sale $sale): bool
    {
        throw_if($sale->status->isCompleted(), InvalidArgumentException::class, 'Cannot delete a completed sale. Please cancel it first.');

        return DB::transaction(function () use ($sale): bool {
            $sale->items()->delete();

            $sale->payments()->delete();

            return (bool) $sale->delete();
        });
    }
}
