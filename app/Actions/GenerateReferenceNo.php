<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ReferenceCounter;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class GenerateReferenceNo
{
    /**
     * @throws Throwable
     */
    public function handle(string $prefix): string
    {
        return DB::transaction(static function () use ($prefix): string {
            $today = today()->format('Ymd');
            $key = "{$prefix}-{$today}";

            $counter = ReferenceCounter::query()->firstOrCreate(['key' => $key], ['last_value' => 0]);
            $counter->increment('last_value');

            /** @var int $count */
            $count = $counter->last_value;

            return sprintf('%s-%s-%s', $prefix, $today, mb_str_pad((string) $count, 4, '0', STR_PAD_LEFT));
        });
    }
}
