<?php

declare(strict_types=1);

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * @implements CastsAttributes<float, never>
 */
final class InvoiceRemainingAmountCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, float>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): float
    {
        $total = (float) ($attributes['total'] ?? 0);
        $paid = (float) ($attributes['paid'] ?? 0);

        return max(0, $total - $paid);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        throw new LogicException('Remaining amount is calculated and cannot be set');
    }
}
