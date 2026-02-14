<?php

declare(strict_types=1);

namespace App\Actions\Unit;

use App\Models\Product;
use App\Models\Unit;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
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
            } elseif (! $fallbackUnit instanceof Unit && $unit->products()->exists()) {
                throw new DomainException('Cannot delete unit with associated products without a fallback unit.');
            }

            return (bool) $unit->delete();
        });
    }

    private function getDefaultUnit(): ?Unit
    {
        return Unit::query()
            ->where('is_active', true)
            ->where(function (Builder $query): void {
                $query->where('short_name', 'pc')
                    ->orWhere('name', 'Piece');
            })
            ->first();
    }
}
