<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
final class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cost_price = $this->faker->randomNumber(6);
        $selling_price = $cost_price + $this->faker->randomNumber(2);

        return [
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'unit_id' => Unit::factory(),
            'name' => $this->faker->name(),
            'sku' => mb_strtoupper(Str::random(8)),
            'barcode' => $this->faker->unique()->ean13(),
            'description' => $this->faker->paragraph(),
            'image' => $this->faker->url(),
            'cost_price' => $cost_price,
            'selling_price' => $selling_price,
            'quantity' => $this->faker->randomNumber(2),
            'alert_quantity' => $this->faker->randomDigitNotZero(),
            'track_inventory' => $this->faker->boolean(90),
            'is_active' => true,
        ];
    }
}
