<?php

declare(strict_types=1);

namespace App\Actions\Warehouse;

use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class UpdateWarehouse
{
    /**
     * @param  array{name?: string, code?: string, email?: string|null, phone?: string|null, address?: string|null, city?: string|null, country?: string|null, is_active?: bool}  $data
     *
     * @throws Throwable
     */
    public function handle(Warehouse $warehouse, array $data): void
    {
        DB::transaction(static function () use ($warehouse, $data): void {
            $warehouse->update($data);
        });
    }
}
