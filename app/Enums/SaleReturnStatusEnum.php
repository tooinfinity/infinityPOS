<?php

declare(strict_types=1);

namespace App\Enums;

enum SaleReturnStatusEnum: string
{
    case Pending = 'pending';
    case Completed = 'completed';

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function toArray(): array
    {
        return array_map(
            static fn (SaleReturnStatusEnum $case): array => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases()
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Completed => 'Completed'
        };
    }
}
