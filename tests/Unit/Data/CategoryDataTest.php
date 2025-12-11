<?php

declare(strict_types=1);

use App\Data\CategoryData;
use App\Data\ExpenseData;
use App\Data\ProductData;
use App\Data\UserData;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\LaravelData\DataCollection;

it('transforms a category model into CategoryData', function (): void {

    $creator = User::factory()->create();
    $updater = User::factory()->create();

    /** @var Category $category */
    $category = Category::factory()
        ->for($creator, 'creator')
        ->for($updater, 'updater')
        ->has(Product::factory()->count(3), 'products')
        ->has(Expense::factory()->count(2), 'expenses')
        ->create([
            'name' => 'Food',
            'code' => 'F01',
            'type' => 'goods',
            'is_active' => true,
        ]);

    $data = CategoryData::fromModel(
        $category->load(['products', 'expenses', 'creator', 'updater'])
    );

    expect($data)
        ->toBeInstanceOf(CategoryData::class)
        ->id->toBe($category->id)
        ->name->toBe('Food')
        ->code->toBe('F01')
        ->type->toBe('goods')
        ->is_active->toBeTrue()
        ->and($data->creator->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($creator->id)
        ->and($data->updater->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($updater->id);

    $products = $data->products->resolve();

    if ($products instanceof DataCollection) {
        expect($products)->toBeInstanceOf(DataCollection::class)
            ->and($products->count())->toBe(3);

        foreach ($products->all() as $p) {
            expect($p)->toBeInstanceOf(ProductData::class);
        }
    } else {
        expect($products)->toBeInstanceOf(Collection::class)
            ->and($products->count())->toBe(3);

        foreach ($products as $p) {
            expect($p)->toBeInstanceOf(ProductData::class);
        }
    }

    $expenses = $data->expenses->resolve();

    if ($expenses instanceof DataCollection) {
        expect($expenses)->toBeInstanceOf(DataCollection::class)
            ->and($expenses->count())->toBe(2);

        foreach ($expenses->all() as $exp) {
            expect($exp)->toBeInstanceOf(ExpenseData::class);
        }
    } else {
        expect($expenses)->toBeInstanceOf(Collection::class)
            ->and($expenses->count())->toBe(2);

        foreach ($expenses as $exp) {
            expect($exp)->toBeInstanceOf(ExpenseData::class);
        }
    }

    expect($data->created_at->toDateTimeString())
        ->toBe($category->created_at->toDateTimeString())
        ->and($data->updated_at->toDateTimeString())
        ->toBe($category->updated_at->toDateTimeString());

});
