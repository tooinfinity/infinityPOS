<?php

declare(strict_types=1);

namespace App\Http\Controllers\Stores;

use App\Actions\Stores\CreateStore;
use App\Actions\Stores\DeleteStore;
use App\Actions\Stores\UpdateStore;
use App\Data\Stores\CreateStoreData;
use App\Data\Stores\StoreData;
use App\Data\Stores\UpdateStoreData;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final readonly class StoreController
{
    public function index(): Response
    {
        $stores = Store::query()
            ->with('creator')
            ->latest()
            ->paginate(50);

        return Inertia::render('stores/index', [
            'stores' => StoreData::collect($stores),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('stores/create');
    }

    public function store(CreateStoreData $data, CreateStore $action): RedirectResponse
    {
        $action->handle($data);

        return to_route('stores.index');
    }

    public function show(Store $store): Response
    {
        $store->load('creator');

        return Inertia::render('stores/show', [
            'store' => StoreData::from($store),
        ]);
    }

    public function edit(Store $store): Response
    {
        return Inertia::render('stores/edit', [
            'store' => StoreData::from($store),
        ]);
    }

    public function update(UpdateStoreData $data, Store $store, UpdateStore $action): RedirectResponse
    {
        $action->handle($store, $data);

        return back();
    }

    public function destroy(Store $store, DeleteStore $action): RedirectResponse
    {
        $action->handle($store);

        return to_route('stores.index');
    }
}
