<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\ExpenseCategoryEnum;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

final class ExpenseData extends Data
{
    public function __construct(
        #[Required]
        #[MapInputName('store_id')]
        public int $storeId,

        #[Nullable]
        #[MapInputName('register_session_id')]
        public ?int $registerSessionId,

        #[Required]
        #[MapInputName('expense_category')]
        public ExpenseCategoryEnum $expenseCategory,

        #[Required, Min(0)]
        public int $amount,

        #[Required]
        public string $description,

        #[Required]
        #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
        #[MapInputName('expense_date')]
        public CarbonImmutable $expenseDate,

        #[Required]
        #[MapInputName('recorded_by')]
        public int $recordedBy = 0,
    ) {}
}
