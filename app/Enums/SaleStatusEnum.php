<?php

declare(strict_types=1);

namespace App\Enums;

enum SaleStatusEnum: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function toArray(): array
    {
        return array_map(
            static fn (SaleStatusEnum $case): array => [
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
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled'
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match ($this) {
            self::Pending => in_array($newStatus, [self::Completed, self::Cancelled], true),
            self::Completed, self::Cancelled => false,
        };
    }

    /**
     * @return list<self>
     */
    public function getValidTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Completed, self::Cancelled],
            self::Completed, self::Cancelled => [],
        };
    }
}
