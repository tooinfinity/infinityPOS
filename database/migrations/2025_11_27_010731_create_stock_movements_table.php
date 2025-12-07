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
            $table->BigInteger('quantity')->comment('Positive = in, Negative = out');
            $table->string('type', 40)->index(); //  ['purchase', 'sale', 'return', 'adjustment', 'transfer']
            $table->string('reference')->nullable()->comment('Link to source document');
            $table->string('batch_number')->nullable()->index();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->references('id')->on('users');
            $table->foreignId('updated_by')->nullable()->references('id')->on('users');
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('store_id')->constrained()->restrictOnDelete();

            $table->timestamps();

            $table->index(['product_id', 'store_id', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }
};
