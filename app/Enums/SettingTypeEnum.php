<?php

declare(strict_types=1);

namespace App\Enums;

enum SettingTypeEnum: string
{
    case STRING = 'string';
    case NUMBER = 'number';
    case BOOLEAN = 'boolean';
    case JSON = 'json';
    case ARRAY = 'array';
    case FILE = 'file';

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function toArray(): array
    {
        return array_map(
            fn (SettingTypeEnum $case): array => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases()
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::STRING => 'String',
            self::NUMBER => 'Number',
            self::BOOLEAN => 'Boolean',
            self::JSON => 'JSON',
            self::ARRAY => 'Array',
            self::FILE => 'File',
        };
    }

    /**
     * @return string|int|float|bool|array<string, mixed>|null
     */
    public function castValue(mixed $value): string|int|float|bool|array|null
    {
        return match ($this) {
            self::STRING => (string) $value,
            self::NUMBER => is_numeric($value) ? (float) $value : 0,
            self::BOOLEAN => (bool) $value,
            self::JSON => is_string($value) ? json_decode($value, true) : $value,
            self::ARRAY => is_array($value) ? $value : (array) $value,
            self::FILE => (string) $value,
        };
    }
}
