<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_return_items', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sale_return_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('batch_id')->nullable()->constrained();

            $table->unsignedInteger('quantity');
            $table->unsignedInteger('unit_price');
            $table->unsignedBigInteger('subtotal');

            $table->timestamps();

            $table->index('sale_return_id');
            $table->index('product_id');
            $table->index('batch_id');
        });
    }
};
