<?php

declare(strict_types=1);

namespace App\Data\Brand;

use App\Models\Brand;
use Illuminate\Support\Optional;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

final class BrandData extends Data
{
    public function __construct(
        public string $name,
        public string|Optional|null $slug,
        public string|Optional|null $logo,
        public bool|Optional $is_active,
    ) {}

    public static function fromModel(Brand $model): self
    {
        return self::from([
            'name' => $model->name,
            'slug' => $model->slug,
            'logo' => $model->logo,
            'is_active' => $model->is_active,
        ]);
    }

    public static function authorize(): bool
    {
        return true;
    }

    public static function rules(ValidationContext $context): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:80', 'unique:brands,name'],
            'slug' => ['nullable', 'string', 'min:3', 'max:120', 'unique:brands,slug'],
            'logo' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public static function messages(...$args): array
    {
        return [
            'name.required' => __('The name field is required.'),
            'name.string' => __('The name must be a string.'),
            'name.min' => __('The name must be at least :min characters.'),
            'name.max' => __('The name may not be greater than :max characters.'),
            'name.unique' => __('The name has already been taken.'),
            'slug.string' => __('The slug must be a string.'),
            'slug.min' => __('The slug must be at least :min characters.'),
            'slug.max' => __('The slug may not be greater than :max characters.'),
            'slug.unique' => __('The slug has already been taken.'),
            'logo.string' => __('The logo must be a string.'),
            'is_active.boolean' => __('The is_active field must be true or false.'),
        ];
    }
}
