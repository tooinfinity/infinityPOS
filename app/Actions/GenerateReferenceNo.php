<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final readonly class GenerateReferenceNo
{
    /**
     * @param  class-string<Model>  $model
     */
    public function handle(string $prefix, string $model): string
    {
        return DB::transaction(function () use ($prefix, $model): string {
            $today = today();

            $count = $model::query()
                ->toBase()
                ->whereDate('created_at', $today)
                ->lockForUpdate()
                ->count() + 1;

            return sprintf(
                '%s-%s-%s',
                $prefix,
                $today->format('Ymd'),
                mb_str_pad((string) $count, 4, '0', STR_PAD_LEFT),
            );
        });
    }
}
