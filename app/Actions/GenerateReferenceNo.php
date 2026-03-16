<?php

declare(strict_types=1);

namespace App\Actions;

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

            DB::statement(
                'INSERT INTO reference_counters (`key`, last_value)
                 VALUES (?, 1)
                 ON DUPLICATE KEY UPDATE last_value = last_value + 1',
                [$key]
            );

            /** @var int $count */
            $count = DB::table('reference_counters')
                ->where('key', $key)
                ->lockForUpdate()
                ->value('last_value');

            return sprintf('%s-%s-%s', $prefix, $today, mb_str_pad((string) $count, 4, '0', STR_PAD_LEFT));
        });
    }
}
