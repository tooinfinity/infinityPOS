<?php

declare(strict_types=1);

namespace App\Http\Requests\PurchaseReturn;

use App\Enums\ReturnStatusEnum;
use App\Models\PurchaseReturnItem;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class RemovePurchaseReturnItemRequest extends FormRequest
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
                /** @var PurchaseReturnItem $item */
                $item = $this->route('purchaseReturnItem');
                $return = $item->purchaseReturn;

                if ($return->status !== ReturnStatusEnum::Pending) {
                    $validator->errors()->add(
                        'status',
                        'Items can only be removed from a return with Pending status. Current status: '.$return->status->label()
                    );
                }
            },
        ];
    }
}
