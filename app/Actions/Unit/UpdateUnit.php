<?php

declare(strict_types=1);

namespace App\Actions\Unit;

use App\Data\Unit\UnitData;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class UpdateUnit
{
    /**
     * @throws Throwable
     */
    public function handle(Unit $unit, UnitData $data): Unit
    {
        return DB::transaction(static function () use ($unit, $data): Unit {
            $updateData = [
                'name' => $data->name ?? $unit->name,
                'short_name' => $data->short_name ?? $unit->short_name,
                'is_active' => $data->is_active ?? $unit->is_active,
            ];

            $unit->update($updateData);

            return $unit->refresh();
        });
    }
}
