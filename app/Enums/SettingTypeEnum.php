<?php

declare(strict_types=1);

namespace App\Enums;

use JsonException;
use Stringable;

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
     * @return string|float|bool|array<mixed>
     *
     * @throws JsonException
     */
    public function castValue(mixed $value): string|float|bool|array
    {
        return match ($this) {
            self::STRING => is_string($value)
                ? $value
                : (is_array($value)
                    ? json_encode($value, JSON_THROW_ON_ERROR)
                    : (is_scalar($value) || $value instanceof Stringable ? (string) $value : '')),
            self::NUMBER => is_int($value) || is_float($value) || (is_string($value) && is_numeric($value))
                ? (float) $value
                : 0.0,
            self::BOOLEAN => (bool) $value,
            self::JSON => is_string($value)
                ? (array) json_decode($value, true, flags: JSON_THROW_ON_ERROR)
                : (is_array($value) ? $value : []),
            self::ARRAY => is_array($value) ? $value : [],
            self::FILE => is_string($value) ? $value : '',
        };
    }
}
