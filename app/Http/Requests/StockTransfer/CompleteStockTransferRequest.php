<?php

declare(strict_types=1);

namespace App\Http\Requests\StockTransfer;

use App\Enums\StockTransferStatusEnum;
use App\Models\StockTransfer;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class CompleteStockTransferRequest extends FormRequest
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
                /** @var StockTransfer $transfer */
                $transfer = $this->route('stockTransfer');
                $transfer->load('items');

                if (! $transfer->status->canTransitionTo(StockTransferStatusEnum::Completed)) {
                    $validator->errors()->add(
                        'status',
                        'Transfer cannot be completed from '.$transfer->status->label().' status.'
                    );
                }

                if ($transfer->items->isEmpty()) {
                    $validator->errors()->add(
                        'items',
                        'Transfer must have at least one item to complete.'
                    );
                }
            },
        ];
    }
}
