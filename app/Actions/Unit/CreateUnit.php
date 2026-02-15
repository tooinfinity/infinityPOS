<?php

declare(strict_types=1);

namespace App\Actions\Unit;

use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateUnit
{
    /**
     * @param  array{name: string, short_name: string, is_active?: bool}  $data
     *
     * @throws Throwable
     */
    public function handle(array $data): Unit
    {
        return DB::transaction(static function () use ($data): Unit {
            $data['is_active'] ??= true;

            return Unit::query()->create($data)->refresh();
        });
    }
}
