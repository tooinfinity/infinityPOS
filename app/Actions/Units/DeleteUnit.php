<?php

declare(strict_types=1);

namespace App\Actions\Units;

use App\Models\Unit;

final readonly class DeleteUnit
{
    public function handle(Unit $unit): void
    {
        $unit->update([
            'created_by' => null,
        ]);
        $unit->delete();
    }
}
