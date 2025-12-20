<?php

declare(strict_types=1);

namespace App\Data\Suppliers;

use App\Data\Users\UserData;
use Spatie\LaravelData\Attributes\AutoLazy;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

#[AutoLazy]
final class SupplierData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $phone,
        public ?string $email,
        public ?string $address,
        public ?string $article,
        public ?string $nif,
        public ?string $nis,
        public ?string $rc,
        public ?string $rib,
        public bool $is_active,
        public Lazy|UserData|null $creator,
        public Lazy|UserData|null $updater,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $created_at,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $updated_at,
    ) {}
}
