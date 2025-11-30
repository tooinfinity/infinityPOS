<?php

declare(strict_types=1);

namespace App\Models;

use App\QueryBuilders\CompanyQueryBuilder;
use Carbon\CarbonInterface;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string|null $email
 * @property-read string|null $phone
 * @property-read string|null $phone_secondary
 * @property-read string|null $address
 * @property-read string|null $city
 * @property-read string|null $state
 * @property-read string|null $zip
 * @property-read string|null $country
 * @property-read string|null $logo
 * @property-read string|null $website
 * @property-read string|null $description
 * @property-read string $currency
 * @property-read string $currency_symbol
 * @property-read string $timezone
 * @property-read string $date_format
 * @property-read int|null $business_identifier_id
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read BusinessIdentifier|null $businessIdentifier
 */
final class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<BusinessIdentifier, $this>
     */
    public function businessIdentifier(): BelongsTo
    {
        return $this->belongsTo(BusinessIdentifier::class);
    }

    /**
     * @return CompanyQueryBuilder<self>
     */
    public function newEloquentBuilder(Builder $query): CompanyQueryBuilder
    {
        return new CompanyQueryBuilder($query);
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'name' => 'string',
            'email' => 'string',
            'phone' => 'string',
            'phone_secondary' => 'string',
            'address' => 'string',
            'city' => 'string',
            'state' => 'string',
            'zip' => 'string',
            'country' => 'string',
            'logo' => 'string',
            'website' => 'string',
            'description' => 'string',
            'currency' => 'string',
            'currency_symbol' => 'string',
            'timezone' => 'string',
            'date_format' => 'string',
            'business_identifier_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
