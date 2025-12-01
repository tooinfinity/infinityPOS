<?php

declare(strict_types=1);

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * @implements CastsAttributes<float, never>
 */
final class InvoicePaymentProgressCast implements CastsAttributes
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

        return $total > 0 ? min(100, ($paid / $total) * 100) : 0;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        throw new LogicException('Payment progress is calculated and cannot be set');
    }
}
