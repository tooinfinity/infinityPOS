<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_layers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('store_id')->constrained()->restrictOnDelete();
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->unsignedBigInteger('unit_cost');
            $table->unsignedBigInteger('received_qty');
            $table->unsignedBigInteger('remaining_qty');
            $table->timestamp('received_at');
            $table->timestamps();

            $table->index(['product_id', 'store_id', 'batch_number', 'expiry_date'], 'inventory_layers_lookup');
            $table->index(['product_id', 'store_id', 'remaining_qty', 'received_at', 'id'], 'inventory_layers_fifo');
            $table->index(['product_id', 'store_id', 'received_at']);
        });
    }
};
