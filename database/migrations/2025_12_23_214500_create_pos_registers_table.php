<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_registers', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('store_id')->constrained()->restrictOnDelete();

            $table->string('name');
            $table->string('device_id')->unique();
            $table->boolean('is_active')->default(true)->index();

            $table->unsignedBigInteger('draft_sale_id')->nullable()->index();

            $table->foreignId('created_by')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->references('id')->on('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['store_id', 'is_active']);
        });
    }
};
