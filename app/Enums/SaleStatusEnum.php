<?php

declare(strict_types=1);

namespace App\Enums;

enum SaleStatusEnum: string
{
    case COMPLETED = 'completed';
    case PENDING = 'pending';
    case RETURNED = 'returned';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::COMPLETED => 'Completed',
            self::PENDING => 'Pending',
            self::RETURNED => 'Returned',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::COMPLETED => 'green',
            self::PENDING => 'yellow',
            self::RETURNED => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::COMPLETED => 'check-circle',
            self::PENDING => 'clock',
            self::RETURNED => 'rotate-ccw',
        };
    }

    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    public function canBeReturned(): bool
    {
        return $this === self::COMPLETED;
    }
}
