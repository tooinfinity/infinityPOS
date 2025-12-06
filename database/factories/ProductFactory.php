<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
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
        $cost = $this->faker->randomFloat(2, 1, 500);
        $price = round($cost * $this->faker->randomFloat(2, 1.05, 1.8), 2);

        return [
            'sku' => mb_strtoupper(Str::random(8)),
            'barcode' => (string) $this->faker->unique()->ean13(),
            'name' => $this->faker->unique()->words(asText: true),
            'description' => $this->faker->optional()->sentence(),
            'image' => null,
            'category_id' => null,
            'brand_id' => null,
            'unit_id' => null,
            'tax_id' => null,
            'cost' => $cost,
            'price' => $price,
            'alert_quantity' => $this->faker->numberBetween(0, 10),
            'has_batches' => $this->faker->boolean(20),
            'is_active' => true,
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    /**
     * Mark the product as inactive.
     */
    public function inactive(): self
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }

    /**
     * Mark the product as batch-tracked.
     */
    public function withBatches(): self
    {
        return $this->state(fn (): array => ['has_batches' => true]);
    }
}
