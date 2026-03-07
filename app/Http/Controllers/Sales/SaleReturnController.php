<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sales;

use App\Actions\SaleReturn\CreateSaleReturn;
use App\Actions\SaleReturn\DeleteSaleReturn;
use App\Data\SaleReturn\CreateSaleReturnData;
use App\Http\Requests\SaleReturn\StoreSaleReturnRequest;
use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final readonly class SaleReturnController
{
    public function __construct(
        private CreateSaleReturn $createSaleReturn,
        private DeleteSaleReturn $deleteSaleReturn,
    ) {}

    public function index(): Response
    {
        $returns = SaleReturn::query()
            ->with(['sale', 'warehouse', 'user', 'sale.customer'])
            ->latest()
            ->paginate(20);

        return Inertia::render('returns/index', [
            'returns' => $returns,
        ]);
    }

    public function create(?Sale $sale = null): Response
    {
        $sales = Sale::query()
            ->with(['customer', 'items.product', 'items.batch'])
            ->where('status', 'completed')
            ->where('payment_status', '!=', 'unpaid')
            ->get()
            ->map(fn (Sale $s) => [
                'id' => $s->id,
                'reference_no' => $s->reference_no,
                'sale_date' => $s->sale_date->toDateTimeString(),
                'total_amount' => $s->total_amount,
                'paid_amount' => $s->paid_amount,
                'customer' => $s->customer ? [
                    'id' => $s->customer->id,
                    'name' => $s->customer->name,
                ] : null,
                'items' => $s->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'sku' => $item->product->sku,
                    ],
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ]),
            ]);

        return Inertia::render('returns/create', [
            'sales' => $sales,
            'selectedSale' => $sale?->load(['customer', 'items.product', 'items.batch']),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(StoreSaleReturnRequest $request): RedirectResponse
    {
        $data = CreateSaleReturnData::from($request->validated());
        $data->user_id = $request->user()->id;

        $return = $this->createSaleReturn->handle($data);

        return to_route('returns.show', $return);
    }

    public function show(SaleReturn $return): Response
    {
        $return->load(['sale', 'warehouse', 'user', 'sale.customer', 'items.product', 'items.batch', 'payments']);

        return Inertia::render('returns/show', [
            'return' => [
                'id' => $return->id,
                'reference_no' => $return->reference_no,
                'status' => $return->status->value,
                'return_date' => $return->return_date->toDateTimeString(),
                'total_amount' => $return->total_amount,
                'paid_amount' => $return->paid_amount,
                'payment_status' => $return->payment_status->value,
                'note' => $return->note,
                'sale' => $return->sale ? [
                    'id' => $return->sale->id,
                    'reference_no' => $return->sale->reference_no,
                    'customer' => $return->sale->customer ? [
                        'id' => $return->sale->customer->id,
                        'name' => $return->sale->customer->name,
                    ] : null,
                ] : null,
                'warehouse' => [
                    'id' => $return->warehouse->id,
                    'name' => $return->warehouse->name,
                ],
                'user' => $return->user ? [
                    'id' => $return->user->id,
                    'name' => $return->user->name,
                ] : null,
                'items' => $return->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'sku' => $item->product->sku,
                    ],
                    'batch' => $item->batch ? [
                        'id' => $item->batch->id,
                        'batch_number' => $item->batch->batch_number,
                    ] : null,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ]),
                'created_at' => $return->created_at->toDateTimeString(),
            ],
        ]);
    }

    public function edit(SaleReturn $return): Response
    {
        $return->load(['sale', 'warehouse', 'items.product', 'items.batch']);

        return Inertia::render('returns/edit', [
            'return' => $return,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function destroy(SaleReturn $return): RedirectResponse
    {
        $this->deleteSaleReturn->handle($return);

        return to_route('returns.index');
    }
}
