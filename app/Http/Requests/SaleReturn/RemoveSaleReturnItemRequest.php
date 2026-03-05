<?php

declare(strict_types=1);

namespace App\Http\Requests\SaleReturn;

use App\Enums\ReturnStatusEnum;
use App\Models\SaleReturnItem;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class RemoveSaleReturnItemRequest extends FormRequest
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
                /** @var SaleReturnItem $item */
                $item = $this->route('saleReturnItem');
                $return = $item->saleReturn;

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
