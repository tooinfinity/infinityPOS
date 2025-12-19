<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('sku')->nullable()->unique();
            $table->string('barcode')->nullable()->index();
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->unsignedBigInteger('cost');
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('alert_quantity');
            // Batch tracking option
            $table->boolean('has_batches');
            $table->boolean('is_active');

            $table->foreignId('category_id')->nullable()->constrained();
            $table->foreignId('brand_id')->nullable()->constrained();
            $table->foreignId('unit_id')->nullable()->constrained();
            $table->foreignId('tax_id')->nullable()->constrained();
            $table->foreignId('created_by')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->references('id')->on('users')->nullOnDelete();

            $table->timestamps();

            $table->index('price');
            $table->index(['category_id', 'brand_id', 'is_active']);
            $table->index(['has_batches', 'is_active']);

        });
    }
};
