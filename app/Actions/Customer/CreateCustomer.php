<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Data\Customer\CreateCustomerData;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateCustomer
{
    /**
     * @throws Throwable
     */
    public function handle(CreateCustomerData $data): Customer
    {
        return DB::transaction(static function () use ($data): Customer {
            $isActive = $data->is_active ?? true;

            return Customer::query()->forceCreate([
                'name' => $data->name,
                'email' => $data->email,
                'phone' => $data->phone,
                'address' => $data->address,
                'city' => $data->city,
                'country' => $data->country,
                'is_active' => $isActive,
            ])->refresh();
        });
    }
}
