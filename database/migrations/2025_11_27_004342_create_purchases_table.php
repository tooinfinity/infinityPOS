<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table): void {
            $table->id();
            $table->string('reference')->unique();

            $table->foreignId('supplier_id')->nullable()->constrained();
            $table->foreignId('store_id')->constrained();

            $table->decimal('subtotal', 15, 2);
            $table->decimal('discount', 15, 2)->nullable();
            $table->decimal('tax', 15, 2)->nullable();
            $table->decimal('total', 15, 2);
            $table->decimal('paid', 15, 2);

            $table->enum('status', ['pending', 'received', 'cancelled'])->default('received')->index();

            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->timestamps();

            $table->index(['store_id', 'status', 'created_at']);
            $table->index(['supplier_id', 'created_at']);
        });
    }
};
