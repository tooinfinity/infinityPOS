<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReturnStatusEnum;
use Carbon\CarbonInterface;
use Database\Factories\PurchaseReturnFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int $id
 * @property-read int $purchase_id
 * @property-read int $warehouse_id
 * @property-read int|null $user_id
 * @property-read string $reference_no
 * @property-read CarbonInterface $return_date
 * @property-read int $total_amount
 * @property-read ReturnStatusEnum $status
 * @property-read string|null $note
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class PurchaseReturn extends Model
{
    /** @use HasFactory<PurchaseReturnFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'purchase_id' => 'integer',
            'warehouse_id' => 'integer',
            'user_id' => 'integer',
            'reference_no' => 'string',
            'return_date' => 'datetime',
            'total_amount' => 'integer',
            'status' => ReturnStatusEnum::class,
            'note' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
