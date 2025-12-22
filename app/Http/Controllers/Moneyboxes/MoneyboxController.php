<?php

declare(strict_types=1);

namespace App\Http\Controllers\Moneyboxes;

use App\Actions\Moneyboxes\CreateMoneybox;
use App\Actions\Moneyboxes\DeleteMoneybox;
use App\Actions\Moneyboxes\UpdateMoneybox;
use App\Data\MoneyboxData;
use App\Data\Moneyboxes\CreateMoneyboxData;
use App\Data\Moneyboxes\UpdateMoneyboxData;
use App\Models\Moneybox;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;
use Throwable;

final readonly class MoneyboxController
{
    public function index(): Response
    {
        $moneyboxes = Moneybox::query()
            ->with(['store', 'creator'])
            ->latest()
            ->paginate(50);

        return Inertia::render('moneyboxes/index', [
            'moneyboxes' => MoneyboxData::collect($moneyboxes),
        ]);
    }

    public function create(): Response
    {
        $stores = Store::query()->latest()->get();

        return Inertia::render('moneyboxes/create', [
            'stores' => $stores,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(CreateMoneyboxData $data, CreateMoneybox $action): RedirectResponse
    {
        $action->handle($data);

        return to_route('moneyboxes.index');
    }

    public function show(Moneybox $moneybox): Response
    {
        $moneybox->load(['store', 'creator', 'transactions']);

        return Inertia::render('moneyboxes/show', [
            'moneybox' => MoneyboxData::from($moneybox),
        ]);
    }

    public function edit(Moneybox $moneybox): Response
    {
        $moneybox->load('store');
        $stores = Store::query()->latest()->get();

        return Inertia::render('moneyboxes/edit', [
            'moneybox' => MoneyboxData::from($moneybox),
            'stores' => $stores,
        ]);
    }

    public function update(UpdateMoneyboxData $data, Moneybox $moneybox, UpdateMoneybox $action): RedirectResponse
    {
        $action->handle($moneybox, $data);

        return back();
    }

    /**
     * @throws Throwable
     */
    public function destroy(Moneybox $moneybox, DeleteMoneybox $action): RedirectResponse
    {
        try {
            $action->handle($moneybox);

            return to_route('moneyboxes.index');
        } catch (InvalidArgumentException $invalidArgumentException) {
            return back()->withErrors(['message' => $invalidArgumentException->getMessage()]);
        }
    }
}
