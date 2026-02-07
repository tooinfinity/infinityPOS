<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $payment_method_id
 * @property int|null $user_id
 * @property string $reference_no
 * @property string $payable_type
 * @property int $payable_id
 * @property int $amount
 * @property CarbonInterface $payment_date
 * @property string|null $note
 * @property CarbonInterface $created_at
 * @property CarbonInterface $updated_at
 */
final class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'payment_method_id' => 'integer',
            'user_id' => 'integer',
            'reference_no' => 'string',
            'payable_type' => 'string',
            'payable_id' => 'integer',
            'amount' => 'integer',
            'payment_date' => 'date',
            'note' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
