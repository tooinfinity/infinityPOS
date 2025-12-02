<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_return_items', function (Blueprint $table): void {
            $table->id();
            $table->decimal('quantity', 15, 2);
            $table->decimal('cost', 15, 2);
            $table->decimal('total', 15, 2);
            $table->string('batch_number')->nullable();

            $table->foreignId('purchase_return_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('purchase_item_id')->nullable()->constrained();

            $table->timestamps();
        });
    }
};
