<?php

declare(strict_types=1);

namespace App\Data\Category;

use App\Models\Category;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

final class CategoryData extends Data
{
    public function __construct(
        public string $name,
        public ?string $description,
        public bool $is_active,
    ) {}

    public static function fromModel(Category $model): self
    {
        return self::from([
            'name' => $model->name,
            'description' => $model->description,
            'is_active' => $model->is_active,
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
            'name' => ['required', 'string', 'min:3', 'max:80', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @param  array<string,string>  ...$args
     * @return array<string, string>
     */
    public static function messages(...$args): array
    {
        return [
            'name.required' => __('The name field is required.'),
            'name.string' => __('The name must be a string.'),
            'name.min' => __('The name must be at least :min characters.'),
            'name.max' => __('The name may not be greater than :max characters.'),
            'name.unique' => __('The name has already been taken.'),
            'description.string' => __('The description must be a string.'),
            'description.max' => __('The description may not be greater than :max characters.'),
            'is_active.boolean' => __('The is_active field must be true or false.'),
        ];
    }
}
