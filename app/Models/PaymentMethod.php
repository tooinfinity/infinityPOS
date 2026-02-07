<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Carbon\CarbonInterface;
use Database\Factories\PaymentMethodFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $code
 * @property-read bool $is_active
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
#[ScopedBy([ActiveScope::class])]
final class PaymentMethod extends Model
{
    /** @use HasFactory<PaymentMethodFactory> */
    use HasFactory;

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
