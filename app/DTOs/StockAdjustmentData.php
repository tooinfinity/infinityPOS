<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\StockAdjustmentTypeEnum;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

final class StockAdjustmentData extends Data
{
    public function __construct(
        #[Required]
        #[MapInputName('store_id')]
        public int $storeId,

        #[Required]
        #[MapInputName('product_id')]
        public int $productId,

        #[Required]
        #[MapInputName('adjustment_type')]
        public StockAdjustmentTypeEnum $adjustmentType,

        #[Required]
        public int $quantity,

        #[Nullable, Min(0)]
        #[MapInputName('unit_cost')]
        public ?int $unitCost = null,

        #[Nullable, Min(0)]
        #[MapInputName('total_cost')]
        public ?int $totalCost = null,

        #[Required]
        public string $reason = '',

        #[Nullable]
        #[MapInputName('adjusted_by')]
        public ?int $adjustedBy = null,
    ) {}

    public function calculatedTotalCost(): ?int
    {
        if ($this->totalCost !== null) {
            return $this->totalCost;
        }

        if ($this->unitCost !== null) {
            return abs($this->quantity) * $this->unitCost;
        }

        return null;
    }
}
