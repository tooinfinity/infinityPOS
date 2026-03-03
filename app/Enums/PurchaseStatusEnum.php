<?php

declare(strict_types=1);

namespace App\Enums;

enum PurchaseStatusEnum: string implements HasStatusTransitions
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

    public function canTransitionTo(HasStatusTransitions $newStatus): bool
    {
        if (! $newStatus instanceof self) {
            return false;
        }

        return match ($this) {
            self::Pending => in_array($newStatus, [self::Ordered, self::Received, self::Cancelled], true),
            self::Ordered => in_array($newStatus, [self::Received, self::Cancelled], true),
            self::Received => false,
            self::Cancelled => false,
        };
    }

    /**
     * @return list<self>
     */
    public function getValidTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Ordered, self::Received, self::Cancelled],
            self::Ordered => [self::Received, self::Cancelled],
            self::Received => [],
            self::Cancelled => [self::Pending],
        };
    }
}
