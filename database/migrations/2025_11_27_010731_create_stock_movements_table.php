<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('store_id')->constrained();

            $table->decimal('quantity', 15, 2)->comment('Positive = in, Negative = out');
            $table->enum('type', ['purchase', 'sale', 'sale_return', 'purchase_return', 'adjustment', 'transfer']);

            $table->nullableMorphs('source');
            $table->string('batch_number')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('user_id')->nullable()->constrained();
            $table->timestamps();

            $table->index(['product_id', 'store_id', 'created_at']);
            $table->index(['batch_number']);
        });
    }
};
