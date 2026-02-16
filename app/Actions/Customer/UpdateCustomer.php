<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Data\Customer\UpdateCustomerData;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Optional;
use Throwable;

final readonly class UpdateCustomer
{
    /**
     * @throws Throwable
     */
    public function handle(Customer $customer, UpdateCustomerData $data): Customer
    {
        return DB::transaction(static function () use ($customer, $data): Customer {
            $updateData = [];

            if (! $data->name instanceof Optional) {
                $updateData['name'] = $data->name;
            }

            if (! $data->email instanceof Optional) {
                $updateData['email'] = $data->email;
            }

            if (! $data->phone instanceof Optional) {
                $updateData['phone'] = $data->phone;
            }

            if (! $data->address instanceof Optional) {
                $updateData['address'] = $data->address;
            }

            if (! $data->city instanceof Optional) {
                $updateData['city'] = $data->city;
            }

            if (! $data->country instanceof Optional) {
                $updateData['country'] = $data->country;
            }

            if (! $data->is_active instanceof Optional) {
                $updateData['is_active'] = $data->is_active;
            }

            $customer->update($updateData);

            return $customer->refresh();
        });
    }
}
