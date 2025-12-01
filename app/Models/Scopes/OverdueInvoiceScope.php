<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Enums\InvoiceStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class OverdueInvoiceScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where(function (Builder $q): void {
            $q->where('status', InvoiceStatusEnum::OVERDUE)
                ->orWhere(function (Builder $inner): void {
                    $inner->whereNotIn('status', [InvoiceStatusEnum::PAID, InvoiceStatusEnum::CANCELLED])
                        ->where('due_at', '<', now());
                });
        });
    }
}
