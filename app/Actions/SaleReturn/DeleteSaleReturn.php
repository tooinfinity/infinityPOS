<?php

declare(strict_types=1);

namespace App\Actions\SaleReturn;

use App\Enums\ReturnStatusEnum;
use App\Models\SaleReturn;
use Illuminate\Support\Facades\DB;
use RuntimeException;
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
            throw new RuntimeException(
                "Can only delete pending sale returns. Current status: {$saleReturn->status->value}"
            );
        }

        $hasRefunds = $saleReturn->payments()
            ->where('amount', '<', 0)
            ->exists();

        throw_if($hasRefunds, RuntimeException::class, 'Cannot delete a sale return that has existing refunds. Please void the refunds first.');
    }
}
