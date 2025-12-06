<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moneybox_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('moneybox_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20)->index(); // ['in', 'out', 'transfer']
            $table->unsignedBigInteger('amount');
            $table->unsignedBigInteger('balance_after')->comment('Balance after transaction');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('expense_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('transfer_to_id')->nullable()->constrained('moneyboxes')->nullOnDelete();
            $table->foreignId('created_by')->references('id')->on('users');
            $table->foreignId('updated_by')->nullable()->references('id')->on('users');

            $table->timestamps();

            $table->index(['moneybox_id', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }
};
