<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\SettingTypeEnum;
use Spatie\LaravelData\Data;

final class SettingData extends Data
{
    public function __construct(
        public string $key,
        public mixed $value,
        public SettingTypeEnum $type = SettingTypeEnum::STRING,
        public string $group = 'general',
        public bool $is_public = false,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            key: is_scalar($data['key'] ?? '') ? (string) ($data['key'] ?? '') : '',
            value: $data['value'] ?? null,
            type: isset($data['type']) && (is_string($data['type']) || is_int($data['type'])) ? SettingTypeEnum::from((string) $data['type']) : SettingTypeEnum::STRING,
            group: is_scalar($data['group'] ?? 'general') ? (string) ($data['group'] ?? 'general') : 'general',
            is_public: (bool) ($data['is_public'] ?? false),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
            'type' => $this->type->value,
            'group' => $this->group,
            'is_public' => $this->is_public,
        ];
    }
}
