<?php

declare(strict_types=1);

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * @implements CastsAttributes<float, never>
 */
final class ProfitMarginCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $cost = (float) ($attributes['cost'] ?? 0);
        $price = (float) ($attributes['price'] ?? 0);

        return $cost <= 0 ? 0.0 : (($price - $cost) / $cost) * 100;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        throw new LogicException('Profit margin is calculated and cannot be set');
    }
}
