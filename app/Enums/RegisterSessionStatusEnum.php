<?php

declare(strict_types=1);

namespace App\Enums;

enum RegisterSessionStatusEnum: string
{
    case OPEN = 'open';
    case CLOSED = 'closed';

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
            self::OPEN => 'Open',
            self::CLOSED => 'Closed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::OPEN => 'green',
            self::CLOSED => 'gray',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::OPEN => 'unlock',
            self::CLOSED => 'lock',
        };
    }

    public function isOpen(): bool
    {
        return $this === self::OPEN;
    }

    public function isClosed(): bool
    {
        return $this === self::CLOSED;
    }
}
