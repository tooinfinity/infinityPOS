<?php

declare(strict_types=1);

namespace App\Http\Requests\Sale;

use App\Enums\SaleStatusEnum;
use App\Models\Sale;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class AddSaleItemRequest extends FormRequest
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
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'batch_id' => ['required', 'integer', 'exists:batches,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'integer', 'min:0'],
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
                $sale = $this->route('sale');
                $saleStatus = $sale instanceof Sale ? $sale->status : null;

                if ($saleStatus !== SaleStatusEnum::Pending) {
                    $validator->errors()->add(
                        'status',
                        'Items can only be added to a sale with Pending status. Current status: '.$saleStatus?->label()
                    );
                }
            },
        ];
    }
}
