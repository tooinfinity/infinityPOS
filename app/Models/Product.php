<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Carbon\CarbonInterface;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int $id
 * @property-read int|null $category_id
 * @property-read int|null $brand_id
 * @property-read int $unit_id
 * @property-read string $name
 * @property-read string $sku
 * @property-read string $barcode
 * @property-read string|null $description
 * @property-read string|null $image
 * @property-read int $cost_price
 * @property-read int $selling_price
 * @property-read int $quantity
 * @property-read int $alert_quantity
 * @property-read bool $track_inventory
 * @property-read bool $is_active
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
#[ScopedBy([ActiveScope::class])]
final class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'category_id' => 'integer',
            'brand_id' => 'integer',
            'unit_id' => 'integer',
            'name' => 'string',
            'sku' => 'string',
            'barcode' => 'string',
            'description' => 'string',
            'image' => 'string',
            'cost_price' => 'integer',
            'selling_price' => 'integer',
            'quantity' => 'integer',
            'alert_quantity' => 'integer',
            'track_inventory' => 'boolean',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
