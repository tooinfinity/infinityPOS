<?php

declare(strict_types=1);

namespace App\Data\Unit;

use App\Models\Unit;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

final class UnitData extends Data
{
    public function __construct(
        public string $name,
        public string $short_name,
        public bool $is_active,
    ) {}

    public static function fromModel(Unit $model): self
    {
        return self::from([
            'name' => $model->name,
            'short_name' => $model->short_name,
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
            'name' => ['required', 'string', 'min:3', 'max:80', 'unique:units,name'],
            'short_name' => ['required', 'string', 'min:1', 'max:20'],
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
            'short_name.required' => __('The short name field is required.'),
            'short_name.string' => __('The short name must be a string.'),
            'short_name.min' => __('The short name must be at least :min characters.'),
            'short_name.max' => __('The short name may not be greater than :max characters.'),
            'is_active.boolean' => __('The is_active field must be true or false.'),
        ];
    }
}
