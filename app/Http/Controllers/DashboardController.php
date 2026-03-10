<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\PaymentStatusEnum;
use App\Enums\PurchaseStatusEnum;
use App\Models\Batch;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Inertia\Inertia;
use Inertia\Response;

final readonly class DashboardController
{
    public function __invoke(): Response
    {
        return Inertia::render('dashboard', [
            'stats' => $this->stats(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function stats(): array
    {
        return [
            'sales' => [
                'today_total' => Sale::query()->today()->completed()->sum('total_amount'),
                'today_count' => Sale::query()->today()->completed()->count(),
                'pending_count' => Sale::query()->pending()->count(),
                'unpaid_count' => Sale::query()
                    ->where('payment_status', PaymentStatusEnum::Unpaid)
                    ->completed()
                    ->count(),
            ],

            'purchases' => [
                'pending_count' => Purchase::query()->pending()->count(),
                'ordered_count' => Purchase::query()->ordered()->count(),
                'unpaid_count' => Purchase::query()
                    ->where('payment_status', PaymentStatusEnum::Unpaid)
                    ->where('status', PurchaseStatusEnum::Received)
                    ->count(),
            ],

            'inventory' => [
                'low_stock_count' => Product::query()->lowStock()->count(),
                'out_of_stock_count' => Product::query()->outOfStock()->count(),
                'expiring_soon_count' => Batch::query()->expiringSoon(30)->inStock()->count(),
                'expired_count' => Batch::query()->expired()->inStock()->count(),
            ],

            'expenses' => [
                'this_month_total' => Expense::query()
                    ->whereBetween('expense_date', [
                        now()->startOfMonth(),
                        now()->endOfMonth(),
                    ])
                    ->sum('amount'),
            ],
        ];
    }
}
