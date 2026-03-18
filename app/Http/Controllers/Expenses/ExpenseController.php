<?php

declare(strict_types=1);

namespace App\Http\Controllers\Expenses;

use App\Actions\Expense\CreateExpense;
use App\Actions\Expense\DeleteExpense;
use App\Actions\Expense\UpdateExpense;
use App\Data\Expense\ExpenseData;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final readonly class ExpenseController
{
    public function index(): Response
    {
        /** @var array{search?: string|null, sort?: string|null, direction?: string|null} */
        $filters = request()->only(['search', 'sort', 'direction']);
        $perPage = request()->integer('per_page');

        return Inertia::render('expenses/index', [
            'expenses' => Expense::query()
                ->paginateWithFilters($filters, $perPage),
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('expenses/create', [
            'categories' => ExpenseCategory::query()
                ->select('id', 'name')
                ->get(),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(ExpenseData $data, CreateExpense $action): RedirectResponse
    {
        $expense = $action->handle($data);

        return to_route('expenses.show', $expense)
            ->with('success', "Expense {$expense->reference_no} created successfully.");
    }

    public function show(Expense $expense): Response
    {
        $expense->load(['expenseCategory', 'user']);

        return Inertia::render('expenses/show', [
            'expense' => $expense,
        ]);
    }

    public function edit(Expense $expense): Response
    {
        $expense->load('expenseCategory');

        return Inertia::render('expenses/edit', [
            'expense' => $expense,
            'categories' => ExpenseCategory::query()
                ->select('id', 'name')
                ->get(),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function update(
        Expense $expense,
        ExpenseData $data,
        UpdateExpense $action,
    ): RedirectResponse {
        $action->handle($expense, $data);

        return to_route('expenses.show', $expense)
            ->with('success', 'Expense updated successfully.');
    }

    /**
     * @throws Throwable
     */
    public function destroy(Expense $expense, DeleteExpense $action): RedirectResponse
    {
        $action->handle($expense);

        return to_route('expenses.index')
            ->with('success', 'Expense deleted.');
    }
}
