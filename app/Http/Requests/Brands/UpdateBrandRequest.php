<?php

declare(strict_types=1);

namespace App\Http\Requests\Brands;

use App\Models\Brand;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        /** @var string $name */
        $name = $this->input('name');
        if ($this->filled('name')) {
            $this->merge([
                'slug' => Str::slug($name),
            ]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $brand = $this->route('brand');
        $brandId = $brand instanceof Brand ? $brand->id : null;

        return [
            'name' => ['required', 'string', 'min:3', 'max:80', Rule::unique('brands', 'name')->ignore($brandId)],
            'slug' => ['nullable', 'string', 'min:3', 'max:120', Rule::unique('brands', 'slug')->ignore($brandId)],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
