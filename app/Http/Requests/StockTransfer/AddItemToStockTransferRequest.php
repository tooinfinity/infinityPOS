<?php

declare(strict_types=1);

namespace App\Http\Requests\StockTransfer;

use App\Enums\StockTransferStatusEnum;
use App\Models\StockTransfer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class AddItemToStockTransferRequest extends FormRequest
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
        ];
    }

    /**
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $transfer = $this->route('stockTransfer');
                $transferStatus = $transfer instanceof StockTransfer ? $transfer->status : null;

                if ($transferStatus !== StockTransferStatusEnum::Pending) {
                    $validator->errors()->add(
                        'status',
                        'Items can only be added to a transfer with Pending status. Current status: '.$transferStatus?->label()
                    );
                }
            },
        ];
    }
}
