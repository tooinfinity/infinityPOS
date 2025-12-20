<?php

declare(strict_types=1);

use App\Jobs\Notifications\SendInvoiceJob;
use App\Mail\InvoiceMail;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

it('sends an invoice email to the client', function (): void {
    Mail::fake();

    $user = User::factory()->create();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $client = Client::factory()->create([
        'created_by' => $user->id,
        'email' => 'client@example.com',
    ]);
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
    $job = new SendInvoiceJob($invoice->id);
    $job->handle();

    // Assert email was queued (since InvoiceMail implements ShouldQueue)
    Mail::assertQueued(InvoiceMail::class, fn (InvoiceMail $mail): bool => $mail->hasTo($client->email));
});

it('does not send email when client has no email', function (): void {
    Mail::fake();

    $user = User::factory()->create();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $client = Client::factory()->create([
        'created_by' => $user->id,
        'email' => null,
    ]);
    $product = Product::factory()->create(['created_by' => $user->id]);

    $sale = Sale::factory()->create([
        'store_id' => $store->id,
        'client_id' => $client->id,
        'created_by' => $user->id,
        'subtotal' => 10000,
        'total' => 10000,
        'paid' => 0,
    ]);

    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 5000,
        'total' => 10000,
    ]);

    $invoice = Invoice::factory()->create([
        'sale_id' => $sale->id,
        'client_id' => $client->id,
        'subtotal' => 10000,
        'total' => 10000,
        'paid' => 0,
        'created_by' => $user->id,
    ]);

    // Run the job
    $job = new SendInvoiceJob($invoice->id);
    $job->handle();

    // Assert no email was sent
    Mail::assertNothingSent();
});

it('does not send email when invoice has no client', function (): void {
    Mail::fake();

    $user = User::factory()->create();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $product = Product::factory()->create(['created_by' => $user->id]);

    $sale = Sale::factory()->create([
        'store_id' => $store->id,
        'client_id' => null,
        'created_by' => $user->id,
        'subtotal' => 10000,
        'total' => 10000,
        'paid' => 0,
    ]);

    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 5000,
        'total' => 10000,
    ]);

    $invoice = Invoice::factory()->create([
        'sale_id' => $sale->id,
        'client_id' => null,
        'subtotal' => 10000,
        'total' => 10000,
        'paid' => 0,
        'created_by' => $user->id,
    ]);

    // Run the job
    $job = new SendInvoiceJob($invoice->id);
    $job->handle();

    // Assert no email was sent
    Mail::assertNothingSent();
});
