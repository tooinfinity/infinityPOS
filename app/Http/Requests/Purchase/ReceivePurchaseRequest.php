<?php

declare(strict_types=1);

namespace App\Http\Requests\Purchase;

use App\Enums\PurchaseStatusEnum;
use App\Models\Purchase;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class ReceivePurchaseRequest extends FormRequest
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
        return [];
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

                if (! $purchase->status->canTransitionTo(PurchaseStatusEnum::Received)) {
                    $validator->errors()->add(
                        'status',
                        'Purchase cannot be received from '.$purchase->status->label().' status.'
                    );
                }

                if ($purchase->items()->exists()) {
                    $validator->errors()->add('items', 'Cannot receive a purchase with no items.');
                }
            },
        ];
    }
}
