<?php

declare(strict_types=1);

namespace App\Data\Invoices;

use Spatie\LaravelData\Attributes\Validation\After;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

final class GenerateInvoiceData extends Data
{
    public function __construct(
        #[Required]
        public string $reference,
        public int $sale_id,
        public ?int $client_id,
        #[Required, Date, WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
        public string $issued_at,
        #[Date, After('issued_at'), WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
        public ?string $due_at,
        public ?string $notes,
        public int $created_by,
    ) {}
}
