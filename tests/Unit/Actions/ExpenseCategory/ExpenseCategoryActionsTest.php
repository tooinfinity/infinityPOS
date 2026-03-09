<?php

declare(strict_types=1);

use App\Actions\ExpenseCategory\DeleteExpenseCategory;
use App\Models\ExpenseCategory;

describe(DeleteExpenseCategory::class, function (): void {
    it('may delete an expense category', function (): void {
        $category = ExpenseCategory::factory()->create();

        $action = resolve(DeleteExpenseCategory::class);

        $result = $action->handle($category);

        expect($result)->toBeTrue()
            ->and(ExpenseCategory::query()->where('id', $category->id)->exists())->toBeFalse();
    });

    it('throws exception when deleting category with expenses', function (): void {
        $category = ExpenseCategory::factory()->create();
        App\Models\Expense::factory()->for($category)->create();

        $action = resolve(DeleteExpenseCategory::class);

        expect(fn () => $action->handle($category))->toThrow(App\Exceptions\InvalidOperationException::class);
    });
});
