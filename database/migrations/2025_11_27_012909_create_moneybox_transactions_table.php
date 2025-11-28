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
            $table->foreignId('moneybox_id')->constrained();

            $table->enum('type', ['in', 'out', 'transfer'])->index();
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);

            // Transfer details (if type = transfer)
            $table->foreignId('transfer_to_moneybox_id')->nullable()->constrained('moneyboxes');

            // Link to source transaction
            $table->morphs('transactionable'); // payments, expenses, etc

            $table->string('reference')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('user_id')->nullable()->constrained();
            $table->timestamps();

            $table->index(['moneybox_id', 'created_at']);
        });
    }
};
