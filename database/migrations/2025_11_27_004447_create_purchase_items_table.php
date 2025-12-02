<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_items', function (Blueprint $table): void {
            $table->id();
            $table->decimal('quantity', 15, 2);
            $table->decimal('cost', 15, 2);
            $table->decimal('discount', 15, 2)->nullable();
            $table->decimal('tax_amount', 15, 2)->nullable();
            $table->decimal('total', 15, 2);
            // Batch/Lot tracking
            $table->string('batch_number')->nullable()->index();
            $table->date('expiry_date')->nullable();
            $table->decimal('remaining_quantity', 15, 2)->nullable()->comment('For FIFO tracking');

            $table->foreignId('purchase_id')->constrained();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();

            $table->timestamps();

            $table->index(['batch_number', 'expiry_date']);
        });
    }
};
