<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sales;

use App\Actions\Customer\CreateCustomer;
use App\Actions\Customer\DeleteCustomer;
use App\Actions\Customer\UpdateCustomer;
use App\Data\Customer\CustomerData;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final readonly class CustomerController
{
    public function index(): Response
    {
        return Inertia::render('customers/index', [
            'customers' => Customer::withInactive()
                ->withCount('sales')
                ->latest()
                ->paginate(25),
            'filters' => request()->query(),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(CustomerData $data, CreateCustomer $action): RedirectResponse
    {
        $customer = $action->handle($data);

        return to_route('customers.show', $customer)
            ->with('success', "Customer '{$customer->name}' created.");
    }

    public function show(Customer $customer): Response
    {
        $customer->loadCount('sales');

        $customer->load([
            'sales' => fn (Relation $q) => $q
                ->latest()
                ->limit(10),
        ]);

        return Inertia::render('customers/show', [
            'customer' => $customer,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function update(
        Customer $customer,
        CustomerData $data,
        UpdateCustomer $action,
    ): RedirectResponse {
        $action->handle($customer, $data);

        return to_route('customers.show', $customer)
            ->with('success', "Customer '{$customer->name}' updated.");
    }

    /**
     * @throws Throwable
     */
    public function destroy(Customer $customer, DeleteCustomer $action): RedirectResponse
    {
        $action->handle($customer);

        return to_route('customers.index')
            ->with('success', 'Customer deleted.');
    }
}
