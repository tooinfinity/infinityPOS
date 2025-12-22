<?php

declare(strict_types=1);

namespace App\Actions\Moneyboxes;

use App\Data\Moneyboxes\UpdateMoneyboxData;
use App\Models\Moneybox;

final readonly class UpdateMoneybox
{
    /**
     * Update moneybox details (name, type, description, etc.).
     * Balance cannot be updated directly - use UpdateMoneyboxBalance or transactions.
     */
    public function handle(Moneybox $moneybox, UpdateMoneyboxData $data): Moneybox
    {
        $updateData = array_filter([
            'name' => $data->name,
            'type' => $data->type,
            'description' => $data->description,
            'bank_name' => $data->bank_name,
            'account_number' => $data->account_number,
            'is_active' => $data->is_active,
            'store_id' => $data->store_id,
            'updated_by' => $data->updated_by,
        ], fn (mixed $value): bool => $value !== null);

        $moneybox->update($updateData);

        return $moneybox;
    }
}
