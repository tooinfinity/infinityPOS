<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentStateEnum: string
{
    case Active = 'active';
    case Voided = 'voided';

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function toArray(): array
    {
        return array_map(
            static fn (PaymentStateEnum $case): array => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases()
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Voided => 'Voided',
        };
    }
}
