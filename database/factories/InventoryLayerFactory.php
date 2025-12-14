<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\InventoryLayer;
use App\Models\Product;
use App\Models\Store;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryLayer>
 */
final class InventoryLayerFactory extends Factory
{
    protected $model = InventoryLayer::class;

    public function definition(): array
    {
        $qty = $this->faker->numberBetween(1, 200);
        $remaining = $this->faker->numberBetween(0, $qty);
        $receivedAt = $this->faker->dateTimeBetween('-2 years', 'now');

        return [
            'product_id' => Product::factory(),
            'store_id' => Store::factory(),
            'batch_number' => $this->faker->optional()->bothify('BATCH-#####'),
            'expiry_date' => $this->faker->optional()->dateTimeBetween('+1 month', '+2 years')?->format('Y-m-d'),
            'unit_cost' => $this->faker->numberBetween(50, 5000), // minor units (e.g., cents)
            'received_qty' => $qty,
            'remaining_qty' => $remaining,
            'received_at' => $receivedAt,
            'created_at' => $receivedAt,
            'updated_at' => $receivedAt,
        ];
    }

    public function available(): self
    {
        return $this->state(function (array $attributes): array {
            $qty = $attributes['received_qty'] ?? $this->faker->numberBetween(1, 200);

            return [
                'received_qty' => $qty,
                'remaining_qty' => $this->faker->numberBetween(1, $qty),
            ];
        });
    }

    public function depleted(): self
    {
        return $this->state(fn (array $attributes): array => [
            'remaining_qty' => 0,
        ]);
    }

    public function full(): self
    {
        return $this->state(fn (array $attributes): array => [
            'remaining_qty' => $attributes['received_qty'] ?? 0,
        ]);
    }

    public function withBatch(?string $batch = null, ?string $expiry = null): self
    {
        return $this->state(fn (array $attributes): array => [
            'batch_number' => $batch ?? $this->faker->bothify('BATCH-#####'),
            'expiry_date' => $expiry,
        ]);
    }

    public function fifoAt(DateTimeInterface $receivedAt): self
    {
        return $this->state(fn (array $attributes): array => [
            'received_at' => $receivedAt,
            'created_at' => $receivedAt,
            'updated_at' => $receivedAt,
        ]);
    }

    public function forProductStore(Product|int $product, Store|int $store): self
    {
        return $this->state(fn (array $attributes): array => [
            'product_id' => $product instanceof Product ? $product->getKey() : $product,
            'store_id' => $store instanceof Store ? $store->getKey() : $store,
        ]);
    }
}
