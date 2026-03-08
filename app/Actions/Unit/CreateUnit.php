<?php

declare(strict_types=1);

namespace App\Actions\Unit;

use App\Data\Unit\UnitData;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateUnit
{
    /**
     * @throws Throwable
     */
    public function handle(UnitData $data): Unit
    {
        return DB::transaction(static fn (): Unit => Unit::query()->forceCreate([
            'name' => $data->name,
            'short_name' => $data->short_name,
            'is_active' => $data->is_active,
        ])->refresh());
    }
}
