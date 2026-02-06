<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentStatusEnum;
use App\Enums\PurchaseStatusEnum;
use Carbon\CarbonInterface;
use Database\Factories\PurchaseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int $id
 * @property-read int $supplier_id
 * @property-read int $warehouse_id
 * @property-read int|null $user_id
 * @property-read string $reference_no
 * @property-read PurchaseStatusEnum $status
 * @property-read CarbonInterface $purchase_date
 * @property-read int $total_amount
 * @property-read int $paid_amount
 * @property-read PaymentStatusEnum $payment_status
 * @property-read string|null $note
 * @property-read string|null $document
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class Purchase extends Model
{
    /** @use HasFactory<PurchaseFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'supplier_id' => 'integer',
            'warehouse_id' => 'integer',
            'user_id' => 'integer',
            'reference_no' => 'string',
            'status' => PurchaseStatusEnum::class,
            'purchase_date' => 'datetime',
            'total_amount' => 'integer',
            'paid_amount' => 'integer',
            'payment_status' => PaymentStatusEnum::class,
            'note' => 'string',
            'document' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
