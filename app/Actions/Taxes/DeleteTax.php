<?php

declare(strict_types=1);

namespace App\Actions\Taxes;

use App\Models\Tax;

final readonly class DeleteTax
{
    public function handle(Tax $tax): void
    {
        $tax->update([
            'created_by' => null,
        ]);
        $tax->delete();
    }
}
