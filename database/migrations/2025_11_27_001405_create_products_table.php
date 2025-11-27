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

            $table->foreignId('category_id')->nullable()->constrained();
            $table->foreignId('brand_id')->nullable()->constrained();
            $table->foreignId('unit_id')->nullable()->constrained();
            $table->foreignId('tax_id')->nullable()->constrained();

            $table->decimal('cost', 15, 2)->default(0);
            $table->decimal('price', 15, 2)->default(0);

            $table->decimal('alert_quantity', 15, 2)->default(0);

            // Batch tracking option
            $table->boolean('has_batches')->default(false)->comment('Enable lot/batch tracking');

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('price');
            $table->index(['category_id', 'is_active']);
        });
    }
};
