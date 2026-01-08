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
        Schema::create('purchases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained('stores');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers');
            $table->string('reference_number', 50)->unique();
            $table->string('invoice_number', 100)->nullable();
            $table->date('purchase_date');
            $table->integer('total_cost'); // in cents
            $table->integer('paid_amount')->default(0); // in cents
            $table->enum('payment_status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'check', 'split'])->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            // Indexes
            $table->index('reference_number');
            $table->index('purchase_date');
            $table->index('invoice_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
