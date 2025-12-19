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
            $table->unsignedBigInteger('quantity');
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('cost');
            $table->unsignedBigInteger('discount')->nullable();
            $table->unsignedBigInteger('tax_amount')->nullable();
            $table->unsignedBigInteger('total');
            $table->string('batch_number')->nullable()->index();
            $table->date('expiry_date')->nullable();

            $table->foreignId('sale_id')->constrained();
            $table->foreignId('product_id')->constrained();

            $table->timestamps();

            $table->index(['batch_number', 'expiry_date']);

        });
    }
};
