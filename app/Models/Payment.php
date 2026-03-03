<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentStateEnum;
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
 * @property PaymentStateEnum $status
 * @property int|null $voided_by
 * @property CarbonInterface|null $voided_at
 * @property string|null $void_reason
 * @property CarbonInterface $created_at
 * @property CarbonInterface $updated_at
 * @property Model $payable
 * @property PaymentMethod $paymentMethod
 * @property User|null $user
 * @property User|null $voidedBy
 */
final class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    /**
     * Sum active payments for a given payable.
     */
    public static function sumForPayable(Sale|SaleReturn|Purchase|PurchaseReturn $payable, bool $lockForUpdate = false): int
    {
        $query = self::query()->activeForPayable($payable::class, $payable->id);

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        /** @var int $amount */
        $amount = $query->sum('amount');

        return $amount;
    }

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
     * @return BelongsTo<User, $this>
     */
    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
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
            'payment_date' => 'datetime',
            'note' => 'string',
            'status' => PaymentStateEnum::class,
            'voided_by' => 'integer',
            'voided_at' => 'datetime',
            'void_reason' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === PaymentStateEnum::Active;
    }

    public function isVoided(): bool
    {
        return $this->status === PaymentStateEnum::Voided;
    }

    public function canBeVoided(): bool
    {
        return $this->isActive();
    }

    public function canBeUnvoided(): bool
    {
        return $this->isVoided();
    }

    /**
     * @param  Builder<Payment>  $query
     * @return Builder<Payment>
     */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('status', PaymentStateEnum::Active);
    }

    /**
     * @param  Builder<Payment>  $query
     * @return Builder<Payment>
     */
    #[Scope]
    protected function voided(Builder $query): Builder
    {
        return $query->where('status', PaymentStateEnum::Voided);
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

    /**
     * @param  Builder<Payment>  $query
     * @return Builder<Payment>
     */
    #[Scope]
    protected function refunds(Builder $query): Builder
    {
        return $query->where('amount', '<', 0);
    }

    /**
     * @param  Builder<Payment>  $query
     * @return Builder<Payment>
     */
    #[Scope]
    protected function activeForPayable(Builder $query, string $payableType, int $payableId): Builder
    {
        return $query->where('payable_type', $payableType)
            ->where('payable_id', $payableId)
            ->where('status', PaymentStateEnum::Active);
    }
}
