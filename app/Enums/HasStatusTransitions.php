<?php

declare(strict_types=1);

namespace App\Enums;

use BackedEnum;

interface HasStatusTransitions extends BackedEnum
{
    public function canTransitionTo(self $newStatus): bool;

    /**
     * @return list<HasStatusTransitions>
     */
    public function getValidTransitions(): array;
}
