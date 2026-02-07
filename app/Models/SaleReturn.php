<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SaleReturnStatusEnum;
use Carbon\CarbonInterface;
use Database\Factories\SaleReturnFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int $id
 * @property-read int $sale_id
 * @property-read int $warehouse_id
 * @property-read int $user_id
 * @property-read string $reference_no
 * @property-read CarbonInterface $return_date
 * @property-read int $total_amount
 * @property-read SaleReturnStatusEnum $status
 * @property-read string|null $note
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class SaleReturn extends Model
{
    /** @use HasFactory<SaleReturnFactory> */
    use HasFactory;

    /**
     * @retrun  array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'sale_id' => 'integer',
            'warehouse_id' => 'integer',
            'user_id' => 'integer',
            'reference_no' => 'string',
            'return_date' => 'datetime',
            'total_amount' => 'integer',
            'status' => SaleReturnStatusEnum::class,
            'note' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
