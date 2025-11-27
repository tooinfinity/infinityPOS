<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sale_id')->constrained();
            $table->foreignId('product_id')->constrained();

            $table->decimal('quantity', 15, 2);
            $table->decimal('price', 15, 2);
            $table->decimal('cost', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->nullable();
            $table->decimal('tax_amount', 15, 2)->nullable();
            $table->decimal('total', 15, 2);

            // Batch/Lot tracking
            $table->string('batch_number')->nullable()->index();
            $table->date('expiry_date')->nullable();

            $table->timestamps();

            $table->index(['batch_number', 'expiry_date']);
        });
    }
};
