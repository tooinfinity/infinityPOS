<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->string('reference')->nullable()->unique();
            $table->enum('type', ['sale', 'purchase', 'expense', 'other'])->index();
            $table->decimal('amount', 15, 2);
            $table->string('method', 20)->index();
            $table->text('notes')->nullable();

            $table->foreignId('related_id')->nullable()->comment('ID of sale/purchase/expense');
            $table->foreignId('moneybox_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->references('id')->on('users');
            $table->foreignId('updated_by')->nullable()->references('id')->on('users');

            $table->timestamps();

            $table->index(['type', 'related_id']);
        });
    }
};
