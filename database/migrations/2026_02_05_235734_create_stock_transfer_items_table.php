<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transfer_items', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('batch_id')->nullable()->constrained();
            $table->unsignedInteger('quantity');

            $table->timestamps();

            $table->index('stock_transfer_id');
            $table->index('product_id');
            $table->index('batch_id');
        });
    }
};
