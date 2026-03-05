<?php

declare(strict_types=1);

namespace App\Http\Requests\Sale;

use App\Enums\SaleStatusEnum;
use App\Models\SaleItem;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class RemoveSaleItemRequest extends FormRequest
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
                /** @var SaleItem $item */
                $item = $this->route('saleItem');
                $sale = $item->sale;

                if ($sale->status !== SaleStatusEnum::Pending) {
                    $validator->errors()->add(
                        'status',
                        'Items can only be removed from a sale with Pending status. Current status: '.$sale->status->label()
                    );
                }
            },
        ];
    }
}
