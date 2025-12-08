<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Category;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;

final class CategoryData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $code,
        public string $type,
        public bool $is_active,
        /** @var Lazy|DataCollection<ProductData> */
        public Lazy|DataCollection $products,
        /** @var Lazy|DataCollection<ExpenseData> */
        public Lazy|DataCollection $expenses,
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}

    public static function fromModel(Category $category): self
    {
        return new self(
            id: $category->id,
            name: $category->name,
            code: $category->code,
            type: $category->type,
            is_active: $category->is_active,
            products: Lazy::whenLoaded('products', $category, fn (): DataCollection => ProductData::collect($category->products)),
            expenses: Lazy::whenLoaded('expenses', $category, fn (): DataCollection => ExpenseData::collect($category->expenses)),
            creator: Lazy::whenLoaded('creator', $category, fn (): UserData => UserData::from($category->creator)
            ),
            updater: Lazy::whenLoaded('updater', $category, fn (): ?UserData => $category->updater ? UserData::from($category->updater) : null
            ),
            created_at: $category->created_at,
            updated_at: $category->updated_at,
        );
    }
}
