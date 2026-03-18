<?php

declare(strict_types=1);

namespace App\Http\Controllers\Expenses;

use App\Actions\ExpenseCategory\CreateExpenseCategory;
use App\Actions\ExpenseCategory\DeleteExpenseCategory;
use App\Actions\ExpenseCategory\UpdateExpenseCategory;
use App\Data\ExpenseCategory\ExpenseCategoryData;
use App\Models\ExpenseCategory;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final readonly class ExpenseCategoryController
{
    public function index(): Response
    {
        /** @var array{search?: string|null, sort?: string|null, direction?: string|null} */
        $filters = request()->only(['search', 'sort', 'direction']);
        $perPage = request()->integer('per_page');

        return Inertia::render('expenses/categories/index', [
            'categories' => ExpenseCategory::withInactive()
                ->paginateWithFilters($filters, $perPage),
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('expenses/categories/create');
    }

    /**
     * @throws Throwable
     */
    public function store(
        ExpenseCategoryData $data,
        CreateExpenseCategory $action,
    ): RedirectResponse {
        $category = $action->handle($data);

        return to_route('expense-categories.index')
            ->with('success', "Category '{$category->name}' created.");
    }

    public function edit(ExpenseCategory $expenseCategory): Response
    {
        return Inertia::render('expenses/categories/edit', [
            'category' => $expenseCategory,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function update(
        ExpenseCategory $expenseCategory,
        ExpenseCategoryData $data,
        UpdateExpenseCategory $action,
    ): RedirectResponse {
        $action->handle($expenseCategory, $data);

        return to_route('expense-categories.index')
            ->with('success', "Category '{$expenseCategory->name}' updated.");
    }

    /**
     * @throws Throwable
     */
    public function destroy(
        ExpenseCategory $expenseCategory,
        DeleteExpenseCategory $action,
    ): RedirectResponse {
        $action->handle($expenseCategory);

        return to_route('expense-categories.index')
            ->with('success', 'Category deleted.');
    }
}
