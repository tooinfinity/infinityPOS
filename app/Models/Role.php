<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $guard_name
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 */
final class Role extends SpatieRole
{
    /**
     * @use HasFactory<RoleFactory>
     */
    use HasFactory;
}
