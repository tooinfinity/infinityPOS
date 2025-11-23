<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\PermissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $guard_name
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 */
final class Permission extends SpatiePermission
{
    /**
     * @use HasFactory<PermissionFactory>
     */
    use HasFactory;
}
