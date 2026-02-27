<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Database\Eloquent\Builder;

final readonly class GenerateReferenceNo
{
    /** @phpstan-ignore-next-line  */
    public function __construct(
        private string $prefix,
        private Builder $query,
    ) {}

    public function handle(): string
    {
        $date = now()->format('Ymd');
        $count = $this->query
            ->whereDate('created_at', today())
            ->count() + 1;

        return $this->prefix.'-'.$date.'-'.mb_str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}
