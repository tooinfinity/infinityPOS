<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('warehouse_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('batch_id')->nullable()->constrained();
            $table->foreignId('user_id')->nullable()->constrained();

            $table->string('type');
            $table->Integer('quantity');
            $table->Integer('previous_quantity');
            $table->Integer('current_quantity');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('note')->nullable();

            $table->timestamp('created_at');

            $table->index(['warehouse_id', 'product_id']);
            $table->index('batch_id');
            $table->index(['reference_type', 'reference_id']);
            $table->index('created_at');
        });
    }
};
