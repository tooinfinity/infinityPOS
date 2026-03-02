<?php

declare(strict_types=1);

namespace App\Actions\Batch;

use Illuminate\Support\Str;

final readonly class BatchNumberGenerator
{
    public function handle(int $productId): string
    {
        return 'BAT-'.now()->format('Ymd-His').'-'.$productId.'-'.mb_strtoupper(Str::random(6));
    }
}
