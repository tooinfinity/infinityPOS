<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table): void {
            $table->id();
            $table->string('reference')->unique();
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('discount')->nullable();
            $table->unsignedBigInteger('tax')->nullable();
            $table->unsignedBigInteger('total');
            $table->unsignedBigInteger('paid');
            $table->string('status')->index();
            $table->text('notes')->nullable();

            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('store_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->references('id')->on('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['store_id', 'status', 'created_at']);
            $table->index(['client_id', 'created_at']);
        });
    }
};
