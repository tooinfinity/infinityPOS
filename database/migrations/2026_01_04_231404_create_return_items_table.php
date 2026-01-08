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
        Schema::create('return_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('return_id')->constrained('returns')->cascadeOnDelete();
            $table->foreignId('sale_item_id')->nullable()->constrained('sale_items');
            $table->foreignId('invoice_item_id')->nullable()->constrained('invoice_items');
            $table->foreignId('product_id')->constrained('products');
            $table->integer('quantity');
            $table->integer('unit_price'); // in cents
            $table->integer('unit_cost'); // in cents
            $table->integer('subtotal'); // in cents
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_items');
    }
};
