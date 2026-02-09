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

    public function active(): self
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => true,
        ]);
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    public function forCategory(Category $category): self
    {
        return $this->state(fn (array $attributes): array => [
            'category_id' => $category->id,
        ]);
    }

    public function forBrand(Brand $brand): self
    {
        return $this->state(fn (array $attributes): array => [
            'brand_id' => $brand->id,
        ]);
    }

    public function forUnit(Unit $unit): self
    {
        return $this->state(fn (array $attributes): array => [
            'unit_id' => $unit->id,
        ]);
    }

    public function withoutBrand(): self
    {
        return $this->state(fn (array $attributes): array => [
            'brand_id' => null,
        ]);
    }

    public function withPricing(int $costPrice, int $sellingPrice): self
    {
        return $this->state(fn (array $attributes): array => [
            'cost_price' => $costPrice,
            'selling_price' => $sellingPrice,
        ]);
    }

    public function withMargin(int $costPrice, int $marginPercent): self
    {
        $sellingPrice = (int) ($costPrice * (1 + $marginPercent / 100));

        return $this->state(fn (array $attributes): array => [
            'cost_price' => $costPrice,
            'selling_price' => $sellingPrice,
        ]);
    }

    public function withQuantity(int $quantity): self
    {
        return $this->state(fn (array $attributes): array => [
            'quantity' => $quantity,
        ]);
    }

    public function outOfStock(): self
    {
        return $this->state(fn (array $attributes): array => [
            'quantity' => 0,
        ]);
    }

    public function lowStock(): self
    {
        return $this->state(function (array $attributes): array {
            /** @var int $alertQuantity */
            $alertQuantity = max($attributes['alert_quantity'] ?? 10, 2);

            return [
                'quantity' => $this->faker->numberBetween(1, $alertQuantity - 1),
                'alert_quantity' => $alertQuantity,
            ];
        });
    }

    public function trackingInventory(): self
    {
        return $this->state(fn (array $attributes): array => [
            'track_inventory' => true,
        ]);
    }

    public function notTrackingInventory(): self
    {
        return $this->state(fn (array $attributes): array => [
            'track_inventory' => false,
        ]);
    }

    public function withoutImage(): self
    {
        return $this->state(fn (array $attributes): array => [
            'image' => null,
        ]);
    }

    public function withoutBarcode(): self
    {
        return $this->state(fn (array $attributes): array => [
            'barcode' => null,
        ]);
    }
}
