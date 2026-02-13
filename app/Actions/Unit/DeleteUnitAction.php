<?php

declare(strict_types=1);

namespace App\Actions\Unit;

use App\Models\Product;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class DeleteUnitAction
{
    /**
     * @throws Throwable
     */
    public function handle(Unit $unit, ?Unit $defaultUnit = null): bool
    {
        return DB::transaction(function () use ($unit, $defaultUnit): bool {
            $fallbackUnit = $defaultUnit ?? $this->getDefaultUnit();

            if ($fallbackUnit instanceof Unit && $fallbackUnit->id !== $unit->id) {
                $unit->products()->each(function (Product $product) use ($fallbackUnit): void {
                    $product->forceFill(['unit_id' => $fallbackUnit->id])->save();
                });
            }

            return (bool) $unit->delete();
        });
    }

    private function getDefaultUnit(): ?Unit
    {
        return Unit::query()
            ->where('is_active', true)
            ->where('short_name', 'pc')
            ->orWhere('name', 'Piece')
            ->first();
    }
}
