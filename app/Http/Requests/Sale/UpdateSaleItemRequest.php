<?php

declare(strict_types=1);

namespace App\Http\Requests\Sale;

use App\Enums\SaleStatusEnum;
use App\Models\SaleItem;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class UpdateSaleItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'batch_id' => ['sometimes', 'integer', 'exists:batches,id'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'unit_price' => ['sometimes', 'integer', 'min:0'],
            'unit_cost' => ['nullable', 'integer', 'min:0'],
        ];
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
                        'Items can only be updated on a sale with Pending status. Current status: '.$sale->status->label()
                    );
                }
            },
        ];
    }
}
