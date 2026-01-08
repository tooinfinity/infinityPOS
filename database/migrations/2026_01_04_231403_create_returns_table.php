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
        Schema::create('returns', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sale_id')->nullable()->constrained('sales');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices');
            $table->foreignId('store_id')->constrained('stores');
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->string('return_number', 50)->unique();
            $table->dateTime('return_date');
            $table->integer('total_amount'); // in cents
            $table->enum('refund_method', ['cash', 'card', 'store_credit']);
            $table->text('reason')->nullable();
            $table->foreignId('processed_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
