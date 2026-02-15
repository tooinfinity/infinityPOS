<?php

declare(strict_types=1);

namespace App\Actions\Warehouse;

use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateWarehouse
{
    /**
     * @param  array{name: string, code: string, email?: string|null, phone?: string|null, address?: string|null, city?: string|null, country?: string|null, is_active?: bool}  $data
     *
     * @throws Throwable
     */
    public function handle(array $data): Warehouse
    {
        return DB::transaction(static function () use ($data): Warehouse {
            $data['is_active'] ??= true;

            return Warehouse::query()->create($data);
        });
    }
}
