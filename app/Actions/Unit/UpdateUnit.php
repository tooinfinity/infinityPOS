<?php

declare(strict_types=1);

namespace App\Actions\Unit;

use App\Data\Unit\UpdateUnitData;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Optional;
use Throwable;

final readonly class UpdateUnit
{
    /**
     * @throws Throwable
     */
    public function handle(Unit $unit, UpdateUnitData $data): Unit
    {
        return DB::transaction(static function () use ($unit, $data): Unit {
            $updateData = [];

            if (! $data->name instanceof Optional) {
                $updateData['name'] = $data->name;
            }
            if (! $data->short_name instanceof Optional) {
                $updateData['short_name'] = $data->short_name;
            }
            if (! $data->is_active instanceof Optional) {
                $updateData['is_active'] = $data->is_active;
            }

            $unit->update($updateData);

            return $unit->refresh();
        });
    }
}
