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
            $table->unsignedBigInteger('quantity');
            $table->unsignedBigInteger('cost');
            $table->unsignedBigInteger('discount')->nullable();
            $table->unsignedBigInteger('tax_amount')->nullable();
            $table->unsignedBigInteger('total');
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();

            $table->foreignId('purchase_id')->constrained();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();

            $table->timestamps();

            $table->index(['batch_number', 'expiry_date']);
        });
    }
};
