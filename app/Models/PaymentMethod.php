<?php

declare(strict_types=1);

namespace App\Models;

use App\Builders\PaymentMethodBuilder;
use App\Models\Scopes\ActiveScope;
use Carbon\CarbonInterface;
use Database\Factories\PaymentMethodFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $code
 * @property-read bool $is_active
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Collection<int, Payment> $payments
 *
 * @method static PaymentMethodBuilder query()
 */
#[ScopedBy([ActiveScope::class])]
final class PaymentMethod extends Model
{
    /** @use HasFactory<PaymentMethodFactory> */
    use HasFactory;

    public static function withInactive(): PaymentMethodBuilder
    {
        return self::query()->withoutGlobalScope(ActiveScope::class);
    }

    public function newEloquentBuilder(mixed $query): PaymentMethodBuilder
    {
        return new PaymentMethodBuilder($query);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'name' => 'string',
            'code' => 'string',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
