<?php

declare(strict_types=1);

namespace App\Actions\PurchaseReturn;

use App\Enums\ReturnStatusEnum;
use App\Exceptions\InvalidOperationException;
use App\Models\PurchaseReturn;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class DeletePurchaseReturn
{
    /**
     * @throws Throwable
     */
    public function handle(PurchaseReturn $return): bool
    {
        /** @var bool $result */
        $result = DB::transaction(static function () use ($return): bool {
            if ($return->status !== ReturnStatusEnum::Pending) {
                throw new InvalidOperationException(
                    'delete',
                    'PurchaseReturn',
                    "Only pending returns can be deleted. Current status: {$return->status->label()}."
                );
            }

            throw_if($return->payments()->active()->exists(), InvalidOperationException::class, 'delete', 'PurchaseReturn', 'Cannot delete a return with active payments.');

            $return->items()->delete();

            return (bool) $return->delete();
        });

        return $result;
    }
}
