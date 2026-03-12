<?php

declare(strict_types=1);

namespace App\Data\Expense;

use App\Models\Expense;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

final class ExpenseData extends Data
{
    public function __construct(
        public int $expense_category_id,
        public int $amount,
        public CarbonInterface $expense_date,
        public ?string $description,
    ) {}

    public static function fromModel(Expense $model): self
    {
        return self::from([
            'expense_category_id' => $model->expense_category_id,
            'amount' => $model->amount,
            'expense_date' => $model->expense_date,
            'description' => $model->description,
        ]);
    }

    //    public static function authorize(): bool
    //    {
    //        return true;
    //    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(ValidationContext $context): array
    {
        return [
            'expense_category_id' => ['required', 'integer', 'exists:expense_categories,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'expense_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @param  array<string, string>  ...$args
     * @return array<string, string>
     */
    public static function messages(...$args): array
    {
        return [
            'expense_category_id.required' => __('An expense category is required.'),
            'expense_category_id.exists' => __('The selected category does not exist.'),
            'amount.required' => __('Amount is required.'),
            'amount.min' => __('Amount must be at least 1.'),
            'expense_date.required' => __('Expense date is required.'),
        ];
    }
}
