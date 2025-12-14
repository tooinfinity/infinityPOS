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
            $table->bigInteger('quantity')->comment('Positive = in, Negative = out');
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('batch_number')->nullable()->index();
            $table->text('notes')->nullable();

            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('store_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->references('id')->on('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['source_type', 'source_id'], 'stock_movements_source_morph_index');
            $table->index(['product_id', 'store_id', 'created_at'], 'stock_movements_product_store_created_at');
            $table->index(['source_type', 'source_id', 'created_at'], 'stock_movements_source_created_at');
        });
    }
};
