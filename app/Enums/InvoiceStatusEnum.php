<?php

declare(strict_types=1);

namespace App\Enums;

enum InvoiceStatusEnum: string
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case PAID = 'paid';
    case OVERDUE = 'overdue';
    case CANCELLED = 'cancelled';

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function toArray(): array
    {
        return array_map(
            fn (InvoiceStatusEnum $case): array => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases()
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SENT => 'Sent',
            self::PAID => 'Paid',
            self::OVERDUE => 'Overdue',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::SENT => 'blue',
            self::PAID => 'green',
            self::OVERDUE => 'red',
            self::CANCELLED => 'red',
        };
    }

    public function isPaid(): bool
    {
        return $this === self::PAID;
    }

    public function isPending(): bool
    {
        return in_array($this, [self::DRAFT, self::SENT, self::OVERDUE], true);
    }
}
