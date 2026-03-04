<?php

declare(strict_types=1);

namespace App\Http\Requests\Purchase;

use App\Enums\PurchaseStatusEnum;
use App\Models\Purchase;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class AddPurchaseItemRequest extends FormRequest
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
                /** @var Purchase $purchase */
                $purchase = $this->route('purchase');

                if ($purchase->status !== PurchaseStatusEnum::Pending) {
                    $validator->errors()->add(
                        'status',
                        'Items can only be added to a purchase with Pending status. Current status: '.$purchase->status->label()
                    );
                }
            },
        ];
    }
}
