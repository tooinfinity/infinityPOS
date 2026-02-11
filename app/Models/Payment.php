<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
     * @return MorphTo<Model, $this>
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<PaymentMethod, $this>
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

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

    /**
     * @param  Builder<Payment>  $query
     * @return Builder<Payment>
     */
    #[Scope]
    protected function recent(Builder $query, int $days = 30): Builder
    {
        return $query->where('payment_date', '>=', now()->subDays($days));
    }

    /**
     * @param  Builder<Payment>  $query
     * @return Builder<Payment>
     */
    #[Scope]
    protected function today(Builder $query): Builder
    {
        return $query->whereDate('payment_date', today());
    }
}
