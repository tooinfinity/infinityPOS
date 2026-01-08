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
        Schema::create('stock_adjustments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained('stores');
            $table->foreignId('product_id')->constrained('products');
            $table->enum('adjustment_type', ['expired', 'damaged', 'manual', 'correction']);
            $table->integer('quantity'); // negative for removal
            $table->integer('unit_cost')->nullable(); // in cents
            $table->integer('total_cost')->nullable(); // in cents
            $table->text('reason');
            $table->foreignId('adjusted_by')->constrained('users');
            $table->timestamp('created_at');

            // Index
            $table->index(['store_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
