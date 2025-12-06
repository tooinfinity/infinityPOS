<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Moneybox;
use App\Models\MoneyboxTransaction;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create()->refresh();

    expect(array_keys($user->toArray()))
        ->toBe([
            'id',
            'name',
            'email',
            'created_at',
            'updated_at',
        ]);
});

test('user relationships', function (): void {
    $user = User::factory()->create()->refresh();
    $client = Client::factory()->create(['created_by' => $user->id]);
    $store = Store::factory()->create(['created_by' => $user->id]);
    $sales = Sale::factory()->count(3)->create(['store_id' => $store->id, 'client_id' => $client->id, 'created_by' => $user->id]);
    $supplier = Supplier::factory()->create(['created_by' => $user->id]);
    $purchases = Purchase::factory()->count(3)->create(['store_id' => $store->id, 'supplier_id' => $supplier->id, 'created_by' => $user->id]);
    $saleReturns = SaleReturn::factory()->count(3)->create(['store_id' => $store->id, 'created_by' => $user->id]);
    $purchaseReturns = PurchaseReturn::factory()->count(3)->create(['store_id' => $store->id, 'created_by' => $user->id]);
    $invoices = Invoice::factory()->count(3)->create(['sale_id' => $sales->first()->id, 'created_by' => $user->id]);
    $payments = Payment::factory()->count(3)->create(['created_by' => $user->id]);
    $expenses = Expense::factory()->count(3)->create(['created_by' => $user->id]);
    $product = Product::factory()->create(['created_by' => $user->id]);
    $stockMovements = StockMovement::factory()->count(3)->create(['product_id' => $product->id, 'store_id' => $store->id, 'created_by' => $user->id]);

    $store2 = Store::factory()->create(['created_by' => $user->id]);

    $stockTransfers = collect(range(1, 3))->map(fn () => StockTransfer::factory()->create([
        'from_store_id' => $store->id,
        'to_store_id' => $store2->id,
        'created_by' => $user->id,
    ])
    );

    $moneyboxes = Moneybox::factory()->count(3)->create([
        'created_by' => $user->id,
        'store_id' => $store->id,
    ]);

    $primaryMoneybox = $moneyboxes->first();
    $moneyboxTransactions = MoneyboxTransaction::factory()->count(3)->create([
        'moneybox_id' => $primaryMoneybox->id,
        'created_by' => $user->id,
    ]);

    expect($user->sales->count())->toBe(3)
        ->and($user->purchases->count())->toBe(3)
        ->and($user->saleReturns->count())->toBe(3)
        ->and($user->purchaseReturns->count())->toBe(3)
        ->and($user->invoices->count())->toBe(3)
        ->and($user->payments->count())->toBe(3)
        ->and($user->expenses->count())->toBe(3)
        ->and($user->stockMovements->count())->toBe(3)
        ->and($user->stockTransfers->count())->toBe(3)
        ->and($user->moneyboxes->count())->toBe(3)
        ->and($user->moneyboxTransactions->count())->toBe(3);

});
