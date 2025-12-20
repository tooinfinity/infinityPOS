<?php

declare(strict_types=1);

namespace App\Actions\Units;

use App\Data\Units\CreateUnitData;
use App\Models\Unit;

final readonly class CreateUnit
{
    public function handle(CreateUnitData $data): Unit
    {
        return Unit::query()->create([
            'name' => $data->name,
            'short_name' => $data->short_name,
            'is_active' => $data->is_active,
            'created_by' => $data->created_by,
        ]);
    }
}
