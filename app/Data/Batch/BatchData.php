<?php

declare(strict_types=1);

namespace App\Data\Batch;

use App\Models\Batch;
use DateTimeInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

final class BatchData extends Data
{
    public function __construct(
        public int $product_id,
        public int $warehouse_id,
        public string $batch_number,
        public int $cost_amount,
        public int $quantity,
        public DateTimeInterface|string|null $expires_at,
        public ?int $user_id = null,
    ) {}

    public static function fromModel(Batch $model): self
    {
        return self::from([
            'product_id' => $model->product_id,
            'warehouse_id' => $model->warehouse_id,
            'batch_number' => $model->batch_number,
            'cost_amount' => $model->cost_amount,
            'quantity' => $model->quantity,
            'expires_at' => $model->expires_at,
        ]);
    }

    public static function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(ValidationContext $context): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'batch_number' => ['required', 'string', 'max:255'],
            'cost_amount' => ['required', 'integer', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
            'expires_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @param  array<string,string>  ...$args
     * @return array<string, string>
     */
    public static function messages(...$args): array
    {
        return [
            'product_id.required' => __('The product field is required.'),
            'product_id.integer' => __('The product field must be an integer.'),
            'product_id.exists' => __('The selected product does not exist.'),
            'warehouse_id.required' => __('The warehouse field is required.'),
            'warehouse_id.integer' => __('The warehouse field must be an integer.'),
            'warehouse_id.exists' => __('The selected warehouse does not exist.'),
            'batch_number.required' => __('The batch number field is required.'),
            'batch_number.string' => __('The batch number must be a string.'),
            'batch_number.max' => __('The batch number may not be greater than :max characters.'),
            'cost_amount.required' => __('The cost amount field is required.'),
            'cost_amount.integer' => __('The cost amount must be an integer.'),
            'cost_amount.min' => __('The cost amount must be at least :min.'),
            'quantity.required' => __('The quantity field is required.'),
            'quantity.integer' => __('The quantity must be an integer.'),
            'quantity.min' => __('The quantity must be at least :min.'),
            'expires_at.date' => __('The expires at field must be a valid date.'),
        ];
    }
}
