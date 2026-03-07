<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payments;

use App\Actions\Payment\RecordPayment;
use App\Data\Payment\RecordPaymentData;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class PaymentController
{
    public function __construct(
        private RecordPayment $recordPayment,
    ) {}

    public function index(): Response
    {
        $payments = Payment::query()
            ->with(['paymentMethod', 'user'])
            ->latest()
            ->paginate(20);

        return Inertia::render('payments/index', [
            'payments' => $payments,
        ]);
    }

    public function store(): RedirectResponse
    {
        $validated = request()->validate([
            'payable_type' => ['required', 'string', 'in:sales,sale_returns'],
            'payable_id' => ['required', 'integer'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'payment_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $payableType = match ($validated['payable_type']) {
            'sales' => Sale::class,
            'sale_returns' => SaleReturn::class,
        };

        $payable = $payableType::query()->findOrFail($validated['payable_id']);
        $validated['user_id'] = auth()->id();

        $data = RecordPaymentData::from($validated);

        $this->recordPayment->handle($payable, $data);

        return back();
    }

    public function show(Payment $payment): Response
    {
        $payment->load(['paymentMethod', 'user', 'payable']);

        return Inertia::render('payments/show', [
            'payment' => [
                'id' => $payment->id,
                'reference_no' => $payment->reference_no,
                'amount' => $payment->amount,
                'payment_date' => $payment->payment_date->toDateTimeString(),
                'note' => $payment->note,
                'status' => $payment->status->value,
                'payment_method' => $payment->paymentMethod ? [
                    'id' => $payment->paymentMethod->id,
                    'name' => $payment->paymentMethod->name,
                ] : null,
                'user' => $payment->user ? [
                    'id' => $payment->user->id,
                    'name' => $payment->user->name,
                ] : null,
                'payable' => $payment->payable ? [
                    'id' => $payment->payable->id,
                    'reference_no' => $payment->payable->reference_no,
                    'type' => $payment->payable instanceof Sale ? 'sale' : 'sale_return',
                ] : null,
                'created_at' => $payment->created_at->toDateTimeString(),
            ],
        ]);
    }
}
