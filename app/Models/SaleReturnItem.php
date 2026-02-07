<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\SaleReturnItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int $id
 * @property-read int $sale_return_id
 * @property-read int $product_id
 * @property-read int|null $batch_id
 * @property-read int $quantity
 * @property-read int $unit_price
 * @property-read int $subtotal
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class SaleReturnItem extends Model
{
    /** @use HasFactory<SaleReturnItemFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'sale_return_id' => 'integer',
            'product_id' => 'integer',
            'batch_id' => 'integer',
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'subtotal' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
