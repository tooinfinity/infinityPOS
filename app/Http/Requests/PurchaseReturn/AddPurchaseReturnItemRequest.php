<?php

declare(strict_types=1);

namespace App\Http\Requests\PurchaseReturn;

use App\Enums\ReturnStatusEnum;
use App\Models\PurchaseReturn;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class AddPurchaseReturnItemRequest extends FormRequest
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
            'unit_cost' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $return = $this->route('purchaseReturn');
                $returnStatus = $return instanceof PurchaseReturn ? $return->status : null;

                if ($returnStatus !== ReturnStatusEnum::Pending) {
                    $validator->errors()->add(
                        'status',
                        'Items can only be added to a return with Pending status. Current status: '.$returnStatus?->label()
                    );
                }
            },
        ];
    }
}
