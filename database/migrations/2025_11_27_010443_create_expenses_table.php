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
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();

            $table->foreignId('category_id')->nullable()->constrained();
            $table->foreignId('store_id')->nullable()->constrained();
            $table->foreignId('moneybox_id')->nullable()->constrained();
            $table->foreignId('created_by')->references('id')->on('users');
            $table->foreignId('updated_by')->nullable()->references('id')->on('users');

            $table->timestamps();
        });
    }
};
