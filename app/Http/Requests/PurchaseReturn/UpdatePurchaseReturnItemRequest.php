<?php

declare(strict_types=1);

namespace App\Http\Requests\PurchaseReturn;

use App\Enums\ReturnStatusEnum;
use App\Models\PurchaseReturnItem;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class UpdatePurchaseReturnItemRequest extends FormRequest
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
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'unit_cost' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var PurchaseReturnItem $item */
                $item = $this->route('purchaseReturnItem');
                $return = $item->purchaseReturn;

                if ($return->status !== ReturnStatusEnum::Pending) {
                    $validator->errors()->add(
                        'status',
                        'Items can only be updated on a return with Pending status. Current status: '.$return->status->label()
                    );
                }
            },
        ];
    }
}
