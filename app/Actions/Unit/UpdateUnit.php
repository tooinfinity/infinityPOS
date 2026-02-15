<?php

declare(strict_types=1);

namespace App\Actions\Unit;

use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class UpdateUnit
{
    /**
     * @param  array{name?: string, short_name?: string, is_active?: bool}  $data
     *
     * @throws Throwable
     */
    public function handle(Unit $unit, array $data): Unit
    {
        return DB::transaction(static function () use ($unit, $data): Unit {
            $unit->update($data);

            return $unit->refresh();
        });
    }
}
