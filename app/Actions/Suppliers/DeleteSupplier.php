<?php

declare(strict_types=1);

namespace App\Actions\Suppliers;

use App\Models\Supplier;

final readonly class DeleteSupplier
{
    public function handle(Supplier $supplier): void
    {
        $supplier->update([
            'created_by' => null,
        ]);
        $supplier->delete();
    }
}
