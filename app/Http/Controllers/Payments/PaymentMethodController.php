<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payments;

use App\Actions\PaymentMethod\CreatePaymentMethod;
use App\Actions\PaymentMethod\DeletePaymentMethod;
use App\Actions\PaymentMethod\UpdatePaymentMethod;
use App\Data\Payment\PaymentMethodData;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final readonly class PaymentMethodController
{
    public function index(): Response
    {
        return Inertia::render('payment-method/index', [
            'methods' => PaymentMethod::withInactive()
                ->withCount('payments')
                ->latest()
                ->paginate(25),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('payment-method/create');
    }

    /**
     * @throws Throwable
     */
    public function store(PaymentMethodData $data, CreatePaymentMethod $action): RedirectResponse
    {
        $method = $action->handle($data);

        return to_route('payment-methods.index')
            ->with('success', "Payment method '{$method->name}' created.");
    }

    public function edit(PaymentMethod $paymentMethod): Response
    {
        return Inertia::render('payment-method/edit', [
            'method' => $paymentMethod,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function update(
        PaymentMethod $paymentMethod,
        PaymentMethodData $data,
        UpdatePaymentMethod $action,
    ): RedirectResponse {
        $action->handle($paymentMethod, $data);

        return to_route('payment-methods.index')
            ->with('success', "Payment method '{$paymentMethod->name}' updated.");
    }

    /**
     * @throws Throwable
     */
    public function destroy(PaymentMethod $paymentMethod, DeletePaymentMethod $action): RedirectResponse
    {
        $action->handle($paymentMethod);

        return to_route('payment-methods.index')
            ->with('success', 'Payment method deleted.');
    }
}
