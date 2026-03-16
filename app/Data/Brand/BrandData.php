<?php

declare(strict_types=1);

namespace App\Data\Brand;

use App\Models\Brand;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

final class BrandData extends Data
{
    public function __construct(
        public string $name,
        public bool $is_active,
    ) {}

    public static function fromModel(Brand $model): self
    {
        return self::from([
            'name' => $model->name,
            'is_active' => $model->is_active,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function rules(ValidationContext $context): array
    {
        /** @var Brand|null $brand */
        $brand = Request::route('brand');

        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:80',
                Rule::unique('brands', 'name')->ignore($brand?->id),
            ],
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
            'is_active.boolean' => __('The is_active field must be true or false.'),
        ];
    }
}
