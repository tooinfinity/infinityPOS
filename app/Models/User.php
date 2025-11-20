<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RoleEnum;
use Carbon\CarbonInterface;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $email
 * @property-read string $password
 * @property-read string|null $remember_token
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class User extends Authenticatable
{
    /**
     * @use HasFactory<UserFactory>
     */
    use HasFactory;

    use HasRoles;
    use Notifiable;

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'name' => 'string',
            'email' => 'string',
            'password' => 'hashed',
            'remember_token' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function isCashier(): bool
    {
        return $this->hasRole(RoleEnum::CASHIER->value);
    }

    public function isManager(): bool
    {
        return $this->hasRole(RoleEnum::MANAGER->value);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(RoleEnum::ADMIN->value);
    }
}
