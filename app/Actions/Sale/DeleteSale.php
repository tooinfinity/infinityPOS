<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Enums\SaleStatusEnum;
use App\Exceptions\InvalidOperationException;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class DeleteSale
{
    /**
     * @throws Throwable
     */
    public function handle(Sale $sale): bool
    {
        return DB::transaction(static function () use ($sale): bool {
            throw_if($sale->status === SaleStatusEnum::Completed, InvalidOperationException::class, 'delete', 'Sale', 'Completed sales cannot be deleted. Cancel it first.');

            throw_if($sale->payments()->active()->exists(), InvalidOperationException::class, 'delete', 'Sale', 'Cannot delete a sale with active payments.');

            $sale->items()->delete();

            return (bool) $sale->delete();
        });
    }
}
