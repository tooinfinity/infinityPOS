<?php

declare(strict_types=1);

namespace App\Http\Requests\PurchaseReturn;

use App\Enums\ReturnStatusEnum;
use App\Models\PurchaseReturn;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class CompletePurchaseReturnRequest extends FormRequest
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
                /** @var PurchaseReturn $return */
                $return = $this->route('purchaseReturn');
                $return->load('items');

                if (! $return->status->canTransitionTo(ReturnStatusEnum::Completed)) {
                    $validator->errors()->add(
                        'status',
                        'Return cannot be completed from '.$return->status->label().' status.'
                    );
                }

                if ($return->items->isEmpty()) {
                    $validator->errors()->add(
                        'items',
                        'Return must have at least one item to complete.'
                    );
                }
            },
        ];
    }
}
