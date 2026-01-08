<?php

declare(strict_types=1);

namespace App\Enums;

enum InvoicePaymentStatusEnum: string
{
    case UNPAID = 'unpaid';
    case PARTIAL = 'partial';
    case PAID = 'paid';
    case OVERDUE = 'overdue';

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
            self::UNPAID => 'Unpaid',
            self::PARTIAL => 'Partially Paid',
            self::PAID => 'Paid',
            self::OVERDUE => 'Overdue',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::UNPAID => 'gray',
            self::PARTIAL => 'yellow',
            self::PAID => 'green',
            self::OVERDUE => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::UNPAID => 'circle',
            self::PARTIAL => 'alert-circle',
            self::PAID => 'check-circle-2',
            self::OVERDUE => 'alert-triangle',
        };
    }

    public function isPaid(): bool
    {
        return $this === self::PAID;
    }

    public function requiresAction(): bool
    {
        return in_array($this, [self::UNPAID, self::PARTIAL, self::OVERDUE]);
    }
}
