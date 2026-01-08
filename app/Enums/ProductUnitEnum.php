<?php

declare(strict_types=1);

namespace App\Enums;

enum ProductUnitEnum: string
{
    case PIECE = 'piece';
    case GRAM = 'gram';
    case MILLILITER = 'milliliter';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function options(): array
    {
        return array_map(
            fn (ProductUnitEnum $case): array => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::PIECE => 'Piece',
            self::GRAM => 'Gram (g)',
            self::MILLILITER => 'Milliliter (ml)',
        };
    }

    public function abbreviation(): string
    {
        return match ($this) {
            self::PIECE => 'pc',
            self::GRAM => 'g',
            self::MILLILITER => 'ml',
        };
    }

    /**
     * Check if unit requires decimal input (weight/volume)
     */
    public function requiresDecimalInput(): bool
    {
        return $this !== self::PIECE;
    }

    /**
     * Convert display value to storage value
     * e.g., 2.5 kg → 2500 grams
     */
    public function toStorageUnit(float $displayValue): int
    {
        return match ($this) {
            self::PIECE => (int) $displayValue,
            self::GRAM, self::MILLILITER => (int) ($displayValue * 1000),
        };
    }

    /**
     * Convert storage value to display value
     * e.g., 2500 grams → 2.5 kg
     */
    public function toDisplayUnit(int $storageValue): float
    {
        return match ($this) {
            self::PIECE => (float) $storageValue,
            self::GRAM, self::MILLILITER => $storageValue / 1000,
        };
    }

    public function displayUnit(): string
    {
        return match ($this) {
            self::PIECE => 'pieces',
            self::GRAM => 'kg',
            self::MILLILITER => 'L',
        };
    }
}
