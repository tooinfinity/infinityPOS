<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sales;

use App\Actions\Customer\CreateCustomer;
use App\Actions\Customer\DeleteCustomer;
use App\Actions\Customer\UpdateCustomer;
use App\Data\Customer\CreateCustomerData;
use App\Data\Customer\UpdateCustomerData;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final class CustomerController
{
    public function index(): Response
    {
        /** @var string $search */
        $search = request('search', '');
        $customers = Customer::query()
            ->withCount('sales')
            ->search($search)
            ->latest()
            ->paginate(20);

        return Inertia::render('customers/index', [
            'customers' => $customers,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('customers/create');
    }

    /**
     * @throws Throwable
     */
    public function store(StoreCustomerRequest $request, CreateCustomer $createCustomer): RedirectResponse
    {
        $createCustomer->handle(CreateCustomerData::from($request->validated()));

        return to_route('customers.index');
    }

    public function show(Customer $customer): Response
    {
        $customer->load(['sales' => fn (Relation $query) => $query->latest()->take(10)]);

        return Inertia::render('customers/show', [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'is_active' => $customer->is_active,
                'created_at' => $customer->created_at->toDateTimeString(),
                'recent_sales' => $customer->sales->map(fn ($sale) => [
                    'id' => $sale->id,
                    'reference_no' => $sale->reference_no,
                    'total_amount' => $sale->total_amount,
                    'paid_amount' => $sale->paid_amount,
                    'status' => $sale->status->value,
                    'sale_date' => $sale->sale_date->toDateTimeString(),
                ]),
            ],
        ]);
    }

    public function edit(Customer $customer): Response
    {
        return Inertia::render('customers/edit', [
            'customer' => $customer,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function update(UpdateCustomerRequest $request, UpdateCustomer $updateCustomer, Customer $customer): RedirectResponse
    {
        $updateCustomer->handle($customer, UpdateCustomerData::from($request->validated()));

        return to_route('customers.show', $customer);
    }

    /**
     * @throws Throwable
     */
    public function destroy(DeleteCustomer $deleteCustomer, Customer $customer): RedirectResponse
    {
        $deleteCustomer->handle($customer);

        return to_route('customers.index');
    }
}
