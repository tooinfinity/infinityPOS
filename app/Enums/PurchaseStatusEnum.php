<?php

declare(strict_types=1);

namespace App\Enums;

enum PurchaseStatusEnum: string
{
    case Pending = 'pending';
    case Ordered = 'ordered';
    case Received = 'received';
    case Cancelled = 'cancelled';

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function toArray(): array
    {
        return array_map(
            static fn (PurchaseStatusEnum $case): array => [
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
            self::Ordered => 'Ordered',
            self::Received => 'Received',
            self::Cancelled => 'Cancelled'
        };
    }
}
