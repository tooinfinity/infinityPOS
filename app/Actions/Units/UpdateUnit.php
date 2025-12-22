<?php

declare(strict_types=1);

namespace App\Actions\Units;

use App\Data\Units\UpdateUnitData;
use App\Models\Unit;

final readonly class UpdateUnit
{
    public function handle(Unit $unit, UpdateUnitData $data): void
    {
        $updateData = array_filter([
            'name' => $data->name,
            'short_name' => $data->short_name,
            'is_active' => $data->is_active,
        ], static fn (mixed $value): bool => $value !== null);

        $updateData['updated_by'] = $data->updated_by;

        $unit->update($updateData);
    }
}
