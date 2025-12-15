<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class CreateLanguageData extends Data
{
    public function __construct(
        public string $locale,
    ) {}

    public static function rules(): array
    {
        return [
            'locale' => ['required', 'string', 'in:en,fr,ar'],
        ];
    }
}
