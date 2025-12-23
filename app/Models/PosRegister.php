<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\PosRegisterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $device_id
 * @property-read int $store_id
 * @property-read int|null $moneybox_id
 * @property-read bool $is_active
 * @property-read int|null $draft_sale_id
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Store $store
 */
final class PosRegister extends Model
{
    /** @use HasFactory<PosRegisterFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Store, $this>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * @return BelongsTo<Moneybox, $this>
     */
    public function moneybox(): BelongsTo
    {
        return $this->belongsTo(Moneybox::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'name' => 'string',
            'device_id' => 'string',
            'store_id' => 'integer',
            'moneybox_id' => 'integer',
            'is_active' => 'boolean',
            'draft_sale_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
