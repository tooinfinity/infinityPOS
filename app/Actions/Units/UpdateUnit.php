<?php

declare(strict_types=1);

namespace App\Actions\Units;

use App\Data\Units\UpdateUnitData;
use App\Models\Unit;

final readonly class UpdateUnit
{
    public function handle(Unit $unit, UpdateUnitData $data): void
    {
        $unit->update([
            'name' => $data->name,
            'short_name' => $data->short_name,
            'is_active' => $data->is_active,
            'updated_by' => $data->updated_by,
        ]);
    }
}
