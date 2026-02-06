<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentStatusEnum;
use App\Enums\SaleStatusEnum;
use Carbon\CarbonInterface;
use Database\Factories\SaleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int $id
 * @property-read int|null $customer_id
 * @property-read int $warehouse_id
 * @property-read int|null $user_id
 * @property-read string $reference_no
 * @property-read SaleStatusEnum $status
 * @property-read CarbonInterface $sale_date
 * @property-read int $total_amount
 * @property-read int $paid_amount
 * @property-read int $change_amount
 * @property-read PaymentStatusEnum $payment_status
 * @property-read string|null $note
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class Sale extends Model
{
    /** @use HasFactory<SaleFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'customer_id' => 'integer',
            'warehouse_id' => 'integer',
            'user_id' => 'integer',
            'reference_no' => 'string',
            'status' => SaleStatusEnum::class,
            'sale_date' => 'datetime',
            'total_amount' => 'integer',
            'paid_amount' => 'integer',
            'change_amount' => 'integer',
            'payment_status' => PaymentStatusEnum::class,
            'note' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
