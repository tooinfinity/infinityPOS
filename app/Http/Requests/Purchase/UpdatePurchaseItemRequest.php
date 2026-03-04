<?php

declare(strict_types=1);

namespace App\Http\Requests\Purchase;

use App\Enums\PurchaseStatusEnum;
use App\Models\PurchaseItem;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class UpdatePurchaseItemRequest extends FormRequest
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
                /** @var PurchaseItem $item */
                $item = $this->route('purchaseItem');
                $purchase = $item->purchase;

                if ($purchase->status !== PurchaseStatusEnum::Pending) {
                    $validator->errors()->add(
                        'status',
                        'Items can only be updated on a purchase with Pending status. Current status: '.$purchase->status->label()
                    );
                }
            },
        ];
    }
}
