<?php

declare(strict_types=1);

namespace App\Enums;

enum InvoiceStatusEnum: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case PAID = 'paid';
    case PARTIALLY_PAID = 'partially_paid';
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
            self::PENDING => 'Pending',
            self::PAID => 'Paid',
            self::PARTIALLY_PAID => 'Partially Paid',
            self::OVERDUE => 'Overdue',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::PENDING => 'blue',
            self::PAID => 'green',
            self::PARTIALLY_PAID => 'yellow',
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
        return in_array($this, [self::DRAFT, self::PARTIALLY_PAID, self::PENDING, self::OVERDUE], true);
    }
}
