<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Data\Customer\CustomerData;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class UpdateCustomer
{
    /**
     * @throws Throwable
     */
    public function handle(Customer $customer, CustomerData $data): Customer
    {
        return DB::transaction(static function () use ($customer, $data): Customer {
            $updateData = [
                'name' => $data->name ?? $customer->name,
                'email' => $data->email ?? $customer->email,
                'phone' => $data->phone ?? $customer->phone,
                'address' => $data->address ?? $customer->address,
                'city' => $data->city ?? $customer->city,
                'country' => $data->country ?? $customer->country,
                'is_active' => $data->is_active ?? $customer->is_active,
            ];

            $customer->update($updateData);

            return $customer->refresh();
        });
    }
}
