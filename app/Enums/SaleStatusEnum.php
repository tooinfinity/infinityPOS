<?php

declare(strict_types=1);

namespace App\Enums;

enum SaleStatusEnum: string
{
    case COMPLETED = 'completed';
    case PENDING = 'pending';
    case CANCELLED = 'cancelled';

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function toArray(): array
    {
        return array_map(
            fn (SaleStatusEnum $case): array => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases()
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::COMPLETED => 'Completed',
            self::PENDING => 'Pending',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::COMPLETED => 'green',
            self::PENDING => 'yellow',
            self::CANCELLED => 'red',
        };
    }

    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }
}
