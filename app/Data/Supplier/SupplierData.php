<?php

declare(strict_types=1);

namespace App\Data\Supplier;

use App\Models\Supplier;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

final class SupplierData extends Data
{
    public function __construct(
        public string $name,
        public ?string $company_name,
        public ?string $email,
        public ?string $phone,
        public ?string $address,
        public ?string $city,
        public ?string $country,
        public bool $is_active,
    ) {}

    public static function fromModel(Supplier $model): self
    {
        return self::from([
            'name' => $model->name,
            'company_name' => $model->company_name,
            'email' => $model->email,
            'phone' => $model->phone,
            'address' => $model->address,
            'city' => $model->city,
            'country' => $model->country,
            'is_active' => $model->is_active,
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
            'name' => ['required', 'string', 'min:3', 'max:80', 'unique:customers,name'],
            'company_name' => ['nullable', 'string', 'max:80'],
            'email' => ['nullable', 'string', 'email', 'max:120'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
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
            'company_name.string' => __('The company name must be a string.'),
            'company_name.max' => __('The company name may not be greater than :max characters.'),
            'email.string' => __('The email must be a string.'),
            'email.email' => __('The email must be a valid email address.'),
            'email.max' => __('The email may not be greater than :max characters.'),
            'phone.string' => __('The phone must be a string.'),
            'phone.max' => __('The phone may not be greater than :max characters.'),
            'address.string' => __('The address must be a string.'),
            'address.max' => __('The address may not be greater than :max characters.'),
            'city.string' => __('The city must be a string.'),
            'city.max' => __('The city may not be greater than :max characters.'),
            'country.string' => __('The country must be a string.'),
            'country.max' => __('The country may not be greater than :max characters.'),
            'is_active.boolean' => __('The is_active field must be true or false.'),
        ];
    }
}
