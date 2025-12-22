<?php

declare(strict_types=1);

namespace App\Http\Controllers\Moneyboxes;

use App\Data\MoneyboxTransactionData;
use App\Models\Moneybox;
use App\Models\MoneyboxTransaction;
use Inertia\Inertia;
use Inertia\Response;

final readonly class MoneyboxTransactionController
{
    public function index(): Response
    {
        $transactions = MoneyboxTransaction::query()
            ->with(['moneybox', 'payment', 'expense', 'creator'])
            ->latest()
            ->paginate(50);

        return Inertia::render('moneyboxes/transactions/index', [
            'transactions' => MoneyboxTransactionData::collect($transactions),
        ]);
    }

    public function show(Moneybox $moneybox): Response
    {
        $transactions = MoneyboxTransaction::query()
            ->where('moneybox_id', $moneybox->id)
            ->with(['payment', 'expense', 'creator'])
            ->latest()
            ->paginate(50);

        return Inertia::render('moneyboxes/transactions/show', [
            'moneybox' => $moneybox,
            'transactions' => MoneyboxTransactionData::collect($transactions),
        ]);
    }
}
