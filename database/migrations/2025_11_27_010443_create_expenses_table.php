<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('amount');
            $table->text('description')->nullable();

            $table->foreignId('category_id')->nullable()->constrained();
            $table->foreignId('store_id')->nullable()->constrained();
            $table->foreignId('moneybox_id')->nullable()->constrained();
            $table->foreignId('created_by')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->references('id')->on('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['store_id', 'created_at']);
            $table->index(['moneybox_id', 'created_at']);
        });
    }
};
