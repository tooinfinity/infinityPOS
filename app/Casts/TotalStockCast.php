<?php

declare(strict_types=1);

namespace App\Casts;

use App\Models\Product;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * @implements CastsAttributes<float, never>
 */
final class TotalStockCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        /** @var Product $model */
        return (float) $model->stores()->sum('quantity');
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        throw new LogicException('Total stock cannot be set directly');
    }
}
