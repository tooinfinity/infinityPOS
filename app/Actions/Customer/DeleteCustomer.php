<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class DeleteCustomer
{
    /**
     * @throws Throwable
     */
    public function handle(Customer $customer): bool
    {
        return DB::transaction(static function () use ($customer): bool {
            throw_if($customer->sales()->count() > 0, RuntimeException::class, 'Cannot delete customer with associated sales.');

            return (bool) $customer->delete();
        });
    }
}
