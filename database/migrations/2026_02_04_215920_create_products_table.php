<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained();
            $table->foreignId('brand_id')->nullable()->constrained();
            $table->foreignId('unit_id')->constrained();

            $table->string('name')->unique();
            $table->string('sku');
            $table->string('barcode');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->unsignedBigInteger('cost_price');
            $table->unsignedBigInteger('selling_price');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('alert_quantity');
            $table->boolean('track_inventory');
            $table->boolean('is_active');

            $table->timestamps();

            $table->index('sku');
            $table->index('barcode');
            $table->index('category_id');
            $table->index('brand_id');
            $table->index('is_active');
        });
    }
};
