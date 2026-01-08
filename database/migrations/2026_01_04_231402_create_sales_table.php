<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained('stores');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('register_session_id')->nullable()->constrained('register_sessions');
            $table->string('invoice_number', 50)->unique();
            $table->dateTime('sale_date');
            $table->integer('subtotal'); // in cents
            $table->integer('discount_amount')->default(0); // in cents
            $table->integer('total_amount'); // in cents
            $table->enum('payment_method', ['cash', 'card', 'split']);
            $table->integer('amount_paid'); // in cents
            $table->integer('change_given')->default(0); // in cents
            $table->enum('status', ['completed', 'pending', 'returned'])->default('completed');
            $table->text('notes')->nullable();
            $table->foreignId('cashier_id')->constrained('users');
            $table->timestamps();

            // Indexes
            $table->index('invoice_number');
            $table->index('sale_date');
            $table->index(['store_id', 'sale_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
