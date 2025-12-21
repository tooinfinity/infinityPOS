<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sales;

use App\Actions\Sales\CreateSale;
use App\Actions\Sales\DeleteSale;
use App\Actions\Sales\UpdateSale;
use App\Data\SaleData;
use App\Data\Sales\CreateSaleData;
use App\Data\Sales\UpdateSaleData;
use App\Enums\SaleStatusEnum;
use App\Models\Client;
use App\Models\Sale;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;
use Throwable;

final readonly class SaleController
{
    public function index(): Response
    {
        $sales = Sale::with(['client', 'store', 'creator'])
            ->latest()
            ->paginate(20);

        return Inertia::render('sale/index', [
            'sales' => SaleData::collect($sales),
            'statuses' => SaleStatusEnum::toArray(),
        ]);
    }

    public function create(): Response
    {
        $clients = Client::query()->latest()->get();
        $stores = Store::query()->latest()->get();

        return Inertia::render('sale/create', [
            'clients' => $clients,
            'stores' => $stores,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(CreateSaleData $data, CreateSale $action): RedirectResponse
    {
        $action->handle($data);

        return to_route('sales.index');
    }

    public function show(Sale $sale): Response
    {
        $sale->load(['client', 'store', 'creator', 'items.product', 'payments', 'invoice']);

        return Inertia::render('sale/show', [
            'sale' => SaleData::from($sale),
        ]);
    }

    public function edit(Sale $sale): Response
    {
        $sale->load(['client', 'store', 'items.product']);
        $clients = Client::query()->latest()->get();
        $stores = Store::query()->latest()->get();

        return Inertia::render('sale/edit', [
            'sale' => SaleData::from($sale),
            'clients' => $clients,
            'stores' => $stores,
        ]);
    }

    public function update(UpdateSaleData $data, Sale $sale, UpdateSale $action): RedirectResponse
    {
        $action->handle($sale, $data);

        return back();
    }

    /**
     * @throws Throwable
     */
    public function destroy(Sale $sale, DeleteSale $action): RedirectResponse
    {
        try {
            $action->handle($sale);

            return to_route('sales.index');
        } catch (InvalidArgumentException $invalidArgumentException) {
            return back()->withErrors(['message' => $invalidArgumentException->getMessage()]);
        }
    }
}
