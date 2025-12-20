<?php

declare(strict_types=1);

namespace App\Actions\Stores;

use App\Models\Store;

final readonly class DeleteStore
{
    public function handle(Store $store): void
    {
        $store->update([
            'created_by' => null,
        ]);
        $store->delete();
    }
}
