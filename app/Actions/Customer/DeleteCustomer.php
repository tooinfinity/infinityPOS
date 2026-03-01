<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Exceptions\InvalidOperationException;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class DeleteCustomer
{
    /**
     * @throws Throwable
     */
    public function handle(Customer $customer): bool
    {
        return DB::transaction(static function () use ($customer): bool {
            if ($customer->sales()->count() > 0) {
                throw new InvalidOperationException(
                    'delete',
                    'Customer',
                    'Cannot delete customer with associated sales.'
                );
            }

            return (bool) $customer->delete();
        });
    }
}
