<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Enums\PurchaseStatusEnum;
use App\Enums\ReturnStatusEnum;
use App\Enums\SaleStatusEnum;
use App\Exceptions\StateTransitionException;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use Throwable;

final readonly class ValidatePayableCanAcceptPayment
{
    /**
     * @throws Throwable
     */
    public function handle(Sale|SaleReturn|Purchase|PurchaseReturn $payable): void
    {
        $canAccept = match ($payable::class) {
            Sale::class => $payable->status === SaleStatusEnum::Completed && $payable->payment_status->canAcceptPayment(),
            SaleReturn::class => $payable->status === ReturnStatusEnum::Completed && $payable->payment_status->canAcceptPayment(),
            Purchase::class => $payable->status === PurchaseStatusEnum::Received && $payable->payment_status->canAcceptPayment(),
            PurchaseReturn::class => $payable->status === ReturnStatusEnum::Completed && $payable->payment_status->canAcceptPayment(),
        };

        if (! $canAccept) {
            throw new StateTransitionException(
                $payable->status->value,
                'Payment'
            );
        }
    }
}
