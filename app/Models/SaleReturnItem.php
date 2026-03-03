<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\SaleReturnItemFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
 * @property-read SaleReturn $saleReturn
 * @property-read Product $product
 * @property-read Batch|null $batch
 */
final class SaleReturnItem extends Model
{
    /** @use HasFactory<SaleReturnItemFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<SaleReturn, $this>
     */
    public function saleReturn(): BelongsTo
    {
        return $this->belongsTo(SaleReturn::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Batch, $this>
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

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

    /**
     * @param  Builder<SaleReturnItem>  $query
     * @return Builder<SaleReturnItem>
     */
    #[Scope]
    protected function forProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * @param  Builder<SaleReturnItem>  $query
     * @return Builder<SaleReturnItem>
     */
    #[Scope]
    protected function forBatch(Builder $query, ?int $batchId): Builder
    {
        return $batchId !== null
            ? $query->where('batch_id', $batchId)
            : $query->whereNull('batch_id');
    }

    /**
     * @param  Builder<SaleReturnItem>  $query
     * @return Builder<SaleReturnItem>
     */
    #[Scope]
    protected function forOriginalSale(Builder $query, int $saleId): Builder
    {
        return $query->whereHas('saleReturn', fn (Builder $q) => $q->where('sale_id', $saleId));
    }
}
