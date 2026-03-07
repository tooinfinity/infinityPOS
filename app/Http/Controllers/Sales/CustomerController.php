<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sales;

use App\Actions\Customer\CreateCustomer;
use App\Actions\Customer\DeleteCustomer;
use App\Actions\Customer\UpdateCustomer;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;
use Throwable;

final class CustomerController
{
    public function index(): Response
    {
        //
    }

    public function create(): Response
    {
        //
    }

    /**
     * @throws Throwable
     */
    public function store(StoreCustomerRequest $request, CreateCustomer $createCustomer): RedirectResponse
    {
        //
    }

    public function show(Customer $customer): Response
    {
        //
    }

    public function edit(Customer $customer): Response
    {
        //
    }

    /**
     * @throws Throwable
     */
    public function update(UpdateCustomerRequest $request, UpdateCustomer $updateCustomer, Customer $customer): RedirectResponse
    {
        //
    }

    /**
     * @throws Throwable
     */
    public function destroy(DeleteCustomer $deleteCustomer, Customer $customer): RedirectResponse
    {
        //
    }
}
