<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_stock', function (Blueprint $table): void {
            $table->foreignId('store_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->decimal('quantity', 15, 2)->default(0);
            $table->timestamps();

            $table->primary(['store_id', 'product_id']);
        });
    }
};
