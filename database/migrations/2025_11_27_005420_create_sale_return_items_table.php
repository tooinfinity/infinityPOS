<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_return_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('quantity');
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('cost');
            $table->unsignedBigInteger('discount')->nullable();
            $table->unsignedBigInteger('tax_amount')->nullable();
            $table->unsignedBigInteger('total');

            $table->foreignId('sale_return_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('sale_item_id')->nullable()->constrained();

            $table->timestamps();
        });
    }
};
