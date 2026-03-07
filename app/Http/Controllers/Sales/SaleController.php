<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sales;

use App\Actions\Sale\CreateSale;
use App\Actions\Sale\DeleteSale;
use App\Data\Sale\CreateSaleData;
use App\Http\Requests\Sale\StoreSaleRequest;
use App\Http\Requests\Sale\UpdateSaleRequest;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final readonly class SaleController
{
    public function __construct(
        private CreateSale $createSale,
        private DeleteSale $deleteSale,
    ) {}

    public function index(): Response
    {
        $sales = Sale::query()
            ->with(['customer', 'warehouse', 'user'])
            ->latest()
            ->paginate(20);

        return Inertia::render('sales/index', [
            'sales' => $sales,
        ]);
    }

    public function create(): Response
    {
        $products = Product::query()
            ->with(['category', 'batches'])
            ->where('is_active', true)
            ->where('track_inventory', false)
            ->orWhereHas('batches', fn ($query) => $query->where('quantity', '>', 0))
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'selling_price' => $product->selling_price,
                'cost_price' => $product->cost_price,
                'track_inventory' => $product->track_inventory,
                'category' => $product->category?->name,
                'batches' => $product->batches
                    ->filter(fn ($batch) => $batch->quantity > 0)
                    ->map(fn ($batch) => [
                        'id' => $batch->id,
                        'batch_number' => $batch->batch_number,
                        'quantity' => $batch->quantity,
                        'expiry_date' => $batch->expiry_date?->toDateString(),
                    ]),
                'stock' => $product->batches->sum('quantity'),
            ]);

        return Inertia::render('pos/create', [
            'products' => $products,
        ]);
    }

    public function store(StoreSaleRequest $request): RedirectResponse
    {
        $data = CreateSaleData::from($request->validated());
        $data->user_id = $request->user()->id;

        $this->createSale->handle($data);

        return to_route('sales.index');
    }

    public function show(Sale $sale): Response
    {
        $sale->load(['customer', 'warehouse', 'user', 'items.product', 'items.batch', 'payments']);

        return Inertia::render('sales/show', [
            'sale' => [
                'id' => $sale->id,
                'reference_no' => $sale->reference_no,
                'status' => $sale->status->value,
                'sale_date' => $sale->sale_date->toDateTimeString(),
                'total_amount' => $sale->total_amount,
                'paid_amount' => $sale->paid_amount,
                'due_amount' => $sale->due_amount,
                'change_amount' => $sale->change_amount,
                'payment_status' => $sale->payment_status->value,
                'note' => $sale->note,
                'customer' => $sale->customer ? [
                    'id' => $sale->customer->id,
                    'name' => $sale->customer->name,
                    'email' => $sale->customer->email,
                    'phone' => $sale->customer->phone,
                ] : null,
                'warehouse' => [
                    'id' => $sale->warehouse->id,
                    'name' => $sale->warehouse->name,
                ],
                'user' => $sale->user ? [
                    'id' => $sale->user->id,
                    'name' => $sale->user->name,
                ] : null,
                'items' => $sale->items->map(fn ($item) => [
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
                    'unit_cost' => $item->unit_cost,
                    'subtotal' => $item->subtotal,
                    'profit' => $item->profit,
                ]),
                'payments' => $sale->payments->map(fn ($payment) => [
                    'id' => $payment->id,
                    'reference_no' => $payment->reference_no,
                    'amount' => $payment->amount,
                    'payment_date' => $payment->payment_date->toDateTimeString(),
                    'payment_method' => $payment->paymentMethod?->name,
                    'note' => $payment->note,
                    'status' => $payment->status->value,
                ]),
                'created_at' => $sale->created_at->toDateTimeString(),
            ],
        ]);
    }

    public function edit(Sale $sale): Response
    {
        $sale->load(['customer', 'warehouse', 'items.product', 'items.batch']);

        return Inertia::render('sales/edit', [
            'sale' => $sale,
        ]);
    }

    public function update(UpdateSaleRequest $request, Sale $sale): RedirectResponse
    {
        $sale->forceFill($request->validated())->save();

        return to_route('sales.show', $sale);
    }

    public function destroy(Sale $sale): RedirectResponse
    {
        $this->deleteSale->handle($sale);

        return to_route('sales.index');
    }
}
