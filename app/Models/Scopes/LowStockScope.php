<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class LowStockScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $table = $model->getTable();

        $builder->whereRaw(
            sprintf(
                'COALESCE((SELECT SUM(quantity) FROM store_stock WHERE product_id = %1$s.id), 0) <= %1$s.alert_quantity',
                $table,
            )
        );
    }
}
