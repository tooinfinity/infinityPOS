<?php

declare(strict_types=1);

namespace App\Actions\SaleReturn;

use App\Enums\ReturnStatusEnum;
use App\Exceptions\InvalidOperationException;
use App\Models\SaleReturn;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class DeleteSaleReturn
{
    /**
     * @throws Throwable
     */
    public function handle(SaleReturn $return): bool
    {
        /** @var bool $result */
        $result = DB::transaction(static function () use ($return): bool {
            if ($return->status !== ReturnStatusEnum::Pending) {
                throw new InvalidOperationException(
                    'delete',
                    'SaleReturn',
                    "Only pending returns can be deleted. Current status: {$return->status->label()}."
                );
            }

            if ($return->payments()->active()->exists()) {
                throw new InvalidOperationException(
                    'delete',
                    'SaleReturn',
                    'Cannot delete a return with active payments.'
                );
            }

            $return->items()->delete();

            return (bool) $return->delete();
        });

        return $result;
    }
}
