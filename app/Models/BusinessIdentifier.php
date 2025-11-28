<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\BusinessIdentifierFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int $id
 * @property-read string|null $article
 * @property-read string|null $nif
 * @property-read string|null $nis
 * @property-read string|null $rc
 * @property-read string|null $rib
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Company|null $company
 * @property-read Client|null $client
 * @property-read Supplier|null $supplier
 */
final class BusinessIdentifier extends Model
{
    /** @use HasFactory<BusinessIdentifierFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'article' => 'string',
            'nif' => 'string',
            'nis' => 'string',
            'rc' => 'string',
            'rib' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
