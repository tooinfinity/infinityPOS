<?php

declare(strict_types=1);

namespace App\Data\Product;

use App\Models\Product;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

final class ProductData extends Data
{
    public function __construct(
        public string $name,
        public ?string $sku,
        public ?string $barcode,
        public int $unit_id,
        public ?int $category_id,
        public ?int $brand_id,
        public ?string $description,
        public int $cost_price,
        public int $selling_price,
        public int $alert_quantity,
        public bool $track_inventory,
        public bool $is_active,
    ) {}

    public static function fromModel(Product $model): self
    {
        return self::from([
            'name' => $model->name,
            'sku' => $model->sku,
            'barcode' => $model->barcode,
            'unit_id' => $model->unit_id,
            'category_id' => $model->category_id,
            'brand_id' => $model->brand_id,
            'description' => $model->description,
            'cost_price' => $model->cost_price,
            'selling_price' => $model->selling_price,
            'alert_quantity' => $model->alert_quantity,
            'track_inventory' => $model->track_inventory,
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
            'name' => ['required', 'string', 'min:3', 'max:255', 'unique:products,name'],
            'sku' => ['nullable', 'string', 'min:3', 'max:100', 'unique:products,sku'],
            'barcode' => ['nullable', 'string', 'min:3', 'max:100', 'unique:products,barcode'],
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'description' => ['nullable', 'string'],
            'cost_price' => ['required', 'integer', 'min:0'],
            'selling_price' => ['required', 'integer', 'min:0'],
            'alert_quantity' => ['required', 'integer', 'min:0'],
            'track_inventory' => ['required', 'boolean'],
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
            'sku.string' => __('The SKU must be a string.'),
            'sku.min' => __('The SKU must be at least :min characters.'),
            'sku.max' => __('The SKU may not be greater than :max characters.'),
            'sku.unique' => __('The SKU has already been taken.'),
            'barcode.string' => __('The barcode must be a string.'),
            'barcode.min' => __('The barcode must be at least :min characters.'),
            'barcode.max' => __('The barcode may not be greater than :max characters.'),
            'barcode.unique' => __('The barcode has already been taken.'),
            'unit_id.required' => __('The unit field is required.'),
            'unit_id.integer' => __('The unit must be an integer.'),
            'unit_id.exists' => __('The selected unit is invalid.'),
            'category_id.integer' => __('The category must be an integer.'),
            'category_id.exists' => __('The selected category is invalid.'),
            'brand_id.integer' => __('The brand must be an integer.'),
            'brand_id.exists' => __('The selected brand is invalid.'),
            'description.string' => __('The description must be a string.'),
            'cost_price.required' => __('The cost price field is required.'),
            'cost_price.integer' => __('The cost price must be an integer.'),
            'cost_price.min' => __('The cost price must be at least :min.'),
            'selling_price.required' => __('The selling price field is required.'),
            'selling_price.integer' => __('The selling price must be an integer.'),
            'selling_price.min' => __('The selling price must be at least :min.'),
            'alert_quantity.required' => __('The alert quantity field is required.'),
            'alert_quantity.integer' => __('The alert quantity must be an integer.'),
            'alert_quantity.min' => __('The alert quantity must be at least :min.'),
            'track_inventory.required' => __('The track inventory field is required.'),
            'track_inventory.boolean' => __('The track inventory field must be true or false.'),
            'is_active.boolean' => __('The is_active field must be true or false.'),
        ];
    }
}
