<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CashTransactionTypeEnum;
use Carbon\CarbonInterface;
use Database\Factories\CashTransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property-read int $id
 * @property-read int $register_session_id
 * @property-read CashTransactionTypeEnum $transaction_type
 * @property-read int $amount
 * @property-read string|null $reference_type
 * @property-read int|null $reference_id
 * @property-read string|null $description
 * @property-read int $created_by
 * @property-read CarbonInterface $created_at
 */
final class CashTransaction extends Model
{
    /** @use HasFactory<CashTransactionFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @return BelongsTo<RegisterSession, $this>
     */
    public function registerSession(): BelongsTo
    {
        return $this->belongsTo(RegisterSession::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function isInflow(): bool
    {
        return $this->amount > 0;
    }

    public function isOutflow(): bool
    {
        return $this->amount < 0;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'register_session_id' => 'integer',
            'transaction_type' => CashTransactionTypeEnum::class,
            'amount' => 'integer',
            'reference_type' => 'string',
            'reference_id' => 'integer',
            'description' => 'string',
            'created_by' => 'integer',
            'created_at' => 'datetime',
        ];
    }
}
