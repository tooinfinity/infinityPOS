<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payments;

use App\Actions\Payments\ProcessPayment;
use App\Data\PaymentData;
use App\Data\Payments\ProcessPaymentData;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final readonly class PaymentController
{
    public function index(): Response
    {
        $payments = Payment::query()
            ->with(['moneybox', 'creator', 'related'])
            ->latest()
            ->paginate(50);

        return Inertia::render('payments/index', [
            'payments' => PaymentData::collect($payments),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(ProcessPaymentData $data, ProcessPayment $action): RedirectResponse
    {
        $action->handle($data);

        return back();
    }

    public function show(Payment $payment): Response
    {
        $payment->load(['moneybox', 'creator', 'related', 'moneyboxTransactions']);

        return Inertia::render('payments/show', [
            'payment' => PaymentData::from($payment),
        ]);
    }
}
