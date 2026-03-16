<?php

declare(strict_types=1);

namespace App\Data\ExpenseCategory;

use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

final class ExpenseCategoryData extends Data
{
    public function __construct(
        public string $name,
        public ?string $description,
        public bool $is_active,
    ) {}

    public static function fromModel(ExpenseCategory $model): self
    {
        return self::from([
            'name' => $model->name,
            'description' => $model->description,
            'is_active' => $model->is_active,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function rules(ValidationContext $context): array
    {
        /** @var ExpenseCategory|null $expenseCategory */
        $expenseCategory = Request::route('expenseCategory');

        return [
            'name' => [
                'required', 'string', 'min:3', 'max:80',
                Rule::unique('expense_categories', 'name')->ignore($expenseCategory?->id),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @param  array<string, string>  ...$args
     * @return array<string, string>
     */
    public static function messages(...$args): array
    {
        return [
            'name.required' => __('Category name is required.'),
            'name.min' => __('Category name must be at least :min characters.'),
            'name.max' => __('Category name may not exceed :max characters.'),
            'name.unique' => __('This category name is already taken.'),
        ];
    }
}
