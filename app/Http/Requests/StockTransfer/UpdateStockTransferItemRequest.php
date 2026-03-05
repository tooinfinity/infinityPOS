<?php

declare(strict_types=1);

namespace App\Http\Requests\StockTransfer;

use App\Enums\StockTransferStatusEnum;
use App\Models\StockTransferItem;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class UpdateStockTransferItemRequest extends FormRequest
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
        ];
    }

    /**
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var StockTransferItem $item */
                $item = $this->route('stockTransferItem');
                $transfer = $item->stockTransfer;

                if ($transfer->status !== StockTransferStatusEnum::Pending) {
                    $validator->errors()->add(
                        'status',
                        'Items can only be updated on a transfer with Pending status. Current status: '.$transfer->status->label()
                    );
                }
            },
        ];
    }
}
