<?php

declare(strict_types=1);

namespace App\Models;

use App\QueryBuilders\ClientQueryBuilder;
use Carbon\CarbonInterface;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string|null $phone
 * @property-read string|null $email
 * @property-read string|null $address
 * @property-read float $balance
 * @property-read bool $is_active
 * @property-read int|null $business_identifier_id
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read BusinessIdentifier|null $businessIdentifier
 * @property-read Collection<int, Sale> $sales
 * @property-read Collection<int, SaleReturn> $saleReturns
 * @property-read Collection<int, Invoice> $invoices
 */
#[UseEloquentBuilder(ClientQueryBuilder::class)]
final class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<BusinessIdentifier, $this>
     */
    public function businessIdentifier(): BelongsTo
    {
        return $this->belongsTo(BusinessIdentifier::class);
    }

    /**
     * @return HasMany<Sale, $this>
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * @return HasMany<SaleReturn, $this>
     */
    public function saleReturns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }

    /**
     * @return HasMany<Invoice, $this>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'name' => 'string',
            'phone' => 'string',
            'email' => 'string',
            'address' => 'string',
            'balance' => 'decimal:2',
            'is_active' => 'boolean',
            'business_identifier_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
