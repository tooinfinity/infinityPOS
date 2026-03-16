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
            $unit->update([
                'name' => $data->name,
                'short_name' => $data->short_name,
                'is_active' => $data->is_active,
            ]);

            return $unit->refresh();
        });
    }
}
