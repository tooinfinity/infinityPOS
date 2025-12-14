<?php

declare(strict_types=1);

namespace App\Enums;

enum StockTransferStatusEnum: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function toArray(): array
    {
        return array_map(
            fn (StockTransferStatusEnum $case): array => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases()
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'yellow',
            self::COMPLETED => 'green',
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
