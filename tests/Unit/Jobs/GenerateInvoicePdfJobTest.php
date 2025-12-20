<?php

declare(strict_types=1);

use App\Jobs\Documents\GenerateInvoicePdfJob;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use App\Models\User;

it('generates a PDF invoice and stores it', function (): void {
    $user = User::factory()->create();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $client = Client::factory()->create(['created_by' => $user->id]);
    $product = Product::factory()->create(['created_by' => $user->id]);

    $sale = Sale::factory()->create([
        'store_id' => $store->id,
        'client_id' => $client->id,
        'created_by' => $user->id,
        'subtotal' => 10000,
        'tax' => 2000,
        'total' => 12000,
        'paid' => 0,
    ]);

    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 5000,
        'tax_amount' => 2000,
        'total' => 12000,
    ]);

    $invoice = Invoice::factory()->create([
        'sale_id' => $sale->id,
        'client_id' => $client->id,
        'subtotal' => 10000,
        'tax' => 2000,
        'total' => 12000,
        'paid' => 0,
        'created_by' => $user->id,
    ]);

    // Run the job
    $job = new GenerateInvoicePdfJob($invoice->id);
    $job->handle();

    // Verify PDF was created
    $storageDir = storage_path('app/invoices');
    expect(file_exists($storageDir))->toBeTrue();

    $files = glob(sprintf('%s/invoice_%s_*.pdf', $storageDir, $invoice->reference));
    expect($files)->not->toBeEmpty()
        ->and(file_exists($files[0]))->toBeTrue()
        ->and(filesize($files[0]))->toBeGreaterThan(0);

    // Cleanup
    foreach ($files as $file) {
        unlink($file);
    }
});

it('throws exception when invoice does not exist', function (): void {
    $job = new GenerateInvoicePdfJob(99999);
    $job->handle();
})->throws(Illuminate\Database\Eloquent\ModelNotFoundException::class);
