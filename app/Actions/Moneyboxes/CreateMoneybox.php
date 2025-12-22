<?php

declare(strict_types=1);

namespace App\Actions\Moneyboxes;

use App\Data\Moneyboxes\CreateMoneyboxData;
use App\Models\Moneybox;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateMoneybox
{
    /**
     * Create a new moneybox (cash register, bank account, etc.).
     *
     * @throws Throwable
     */
    public function handle(CreateMoneyboxData $data): Moneybox
    {
        return DB::transaction(fn () => Moneybox::query()->create([
            'name' => $data->name,
            'type' => $data->type,
            'description' => $data->description,
            'balance' => 0, // New moneyboxes start with zero balance
            'bank_name' => $data->bank_name,
            'account_number' => $data->account_number,
            'is_active' => $data->is_active,
            'store_id' => $data->store_id,
            'created_by' => $data->created_by,
        ]));
    }
}
