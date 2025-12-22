<?php

declare(strict_types=1);

namespace App\Http\Controllers\Expenses;

use App\Actions\Expenses\CreateExpense;
use App\Actions\Expenses\DeleteExpense;
use App\Actions\Expenses\UpdateExpense;
use App\Data\Expenses\CreateExpenseData;
use App\Data\Expenses\ExpenseData;
use App\Data\Expenses\UpdateExpenseData;
use App\Enums\CategoryTypeEnum;
use App\Models\Category;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final readonly class ExpenseController
{
    public function index(): Response
    {
        $expenses = Expense::query()
            ->with(['category', 'creator'])
            ->latest()
            ->paginate(50);

        return Inertia::render('expenses/index', [
            'expenses' => ExpenseData::collect($expenses),
        ]);
    }

    public function create(): Response
    {
        $categories = Category::query()
            ->where('type', CategoryTypeEnum::EXPENSE)
            ->latest()
            ->get();

        return Inertia::render('expenses/create', [
            'categories' => $categories,
        ]);
    }

    public function store(CreateExpenseData $data, CreateExpense $action): RedirectResponse
    {
        $action->handle($data);

        return to_route('expenses.index');
    }

    public function show(Expense $expense): Response
    {
        $expense->load(['category', 'creator', 'payments']);

        return Inertia::render('expenses/show', [
            'expense' => ExpenseData::from($expense),
        ]);
    }

    public function edit(Expense $expense): Response
    {
        $expense->load('category');
        $categories = Category::query()
            ->where('type', CategoryTypeEnum::EXPENSE)
            ->latest()
            ->get();

        return Inertia::render('expenses/edit', [
            'expense' => ExpenseData::from($expense),
            'categories' => $categories,
        ]);
    }

    public function update(UpdateExpenseData $data, Expense $expense, UpdateExpense $action): RedirectResponse
    {
        $action->handle($expense, $data);

        return back();
    }

    public function destroy(Expense $expense, DeleteExpense $action): RedirectResponse
    {
        $action->handle($expense);

        return to_route('expenses.index');
    }
}
