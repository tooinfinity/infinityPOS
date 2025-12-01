<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SettingTypeEnum;
use Carbon\CarbonInterface;
use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int $id
 * @property-read string $key
 * @property-read string|null $value
 * @property-read SettingTypeEnum $type
 * @property-read string|null $group
 * @property-read string|null $description
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'key' => 'string',
            'value' => 'string',
            'type' => SettingTypeEnum::class,
            'group' => 'string',
            'description' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
