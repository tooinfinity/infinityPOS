<?php

declare(strict_types=1);

namespace App\Data\Invoices;

use Spatie\LaravelData\Data;

final class UpdateInvoiceData extends Data
{
    public function __construct(
        public ?string $reference,
        public ?string $due_at,
        public ?string $notes,
        public int $updated_by,
    ) {}
}
