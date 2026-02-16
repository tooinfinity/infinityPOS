<?php

declare(strict_types=1);

namespace App\Actions\Supplier;

use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class DeleteSupplier
{
    /**
     * @throws Throwable
     */
    public function handle(Supplier $supplier): bool
    {
        return DB::transaction(static function () use ($supplier): bool {
            throw_if($supplier->purchases()->count() > 0, RuntimeException::class, 'Cannot delete supplier with associated purchases.');

            return (bool) $supplier->delete();
        });
    }
}
