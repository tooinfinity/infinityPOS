<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case PENDING = 'pending';
    case PARTIAL = 'partial';
    case PAID = 'paid';

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
            self::PENDING => 'Pending',
            self::PARTIAL => 'Partially Paid',
            self::PAID => 'Paid',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'red',
            self::PARTIAL => 'yellow',
            self::PAID => 'green',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'clock',
            self::PARTIAL => 'alert-circle',
            self::PAID => 'check-circle',
        };
    }

    public function isPaid(): bool
    {
        return $this === self::PAID;
    }

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }
}
