<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StockTransferStatusEnum;
use Carbon\CarbonInterface;
use Database\Factories\StockTransferFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int $id
 * @property-read int $from_warehouse_id
 * @property-read int $to_warehouse_id
 * @property-read int|null $user_id
 * @property-read string $reference_no
 * @property-read StockTransferStatusEnum $status
 * @property-read string|null $note
 * @property-read CarbonInterface $transfer_date
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class StockTransfer extends Model
{
    /** @use HasFactory<StockTransferFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'from_warehouse_id' => 'integer',
            'to_warehouse_id' => 'integer',
            'user_id' => 'integer',
            'reference_no' => 'string',
            'status' => StockTransferStatusEnum::class,
            'note' => 'string',
            'transfer_date' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
