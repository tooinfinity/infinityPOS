<?php

declare(strict_types=1);

namespace App\Enums;

enum ReturnStatusEnum: string implements HasStatusTransitions
{
    case Pending = 'pending';
    case Completed = 'completed';

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function toArray(): array
    {
        return array_map(
            static fn (ReturnStatusEnum $case): array => [
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
            self::Completed => 'Completed'
        };
    }

    public function canTransitionTo(HasStatusTransitions $newStatus): bool
    {
        if (! $newStatus instanceof self) {
            return false;
        }

        return match ($this) {
            self::Pending => $newStatus === self::Completed,
            self::Completed => false,
        };
    }

    /**
     * @return list<self>
     */
    public function getValidTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Completed],
            self::Completed => [],
        };
    }
}
