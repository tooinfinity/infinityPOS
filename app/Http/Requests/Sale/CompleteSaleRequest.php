<?php

declare(strict_types=1);

namespace App\Http\Requests\Sale;

use App\Enums\SaleStatusEnum;
use App\Models\Sale;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class CompleteSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var Sale $sale */
                $sale = $this->route('sale');
                $sale->load('items');

                if (! $sale->status->canTransitionTo(SaleStatusEnum::Completed)) {
                    $validator->errors()->add(
                        'status',
                        'Sale cannot be completed from '.$sale->status->label().' status.'
                    );
                }

                if ($sale->items->isEmpty()) {
                    $validator->errors()->add(
                        'items',
                        'Sale must have at least one item to complete.'
                    );
                }
            },
        ];
    }
}
