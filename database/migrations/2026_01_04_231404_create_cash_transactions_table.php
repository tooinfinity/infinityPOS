<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cash_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('register_session_id')->constrained('register_sessions');
            $table->enum('transaction_type', ['sale', 'expense', 'withdrawal', 'deposit', 'opening', 'closing']);
            $table->integer('amount'); // in cents (can be negative)
            $table->string('reference_type')->nullable(); // polymorphic
            $table->unsignedBigInteger('reference_id')->nullable(); // polymorphic
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('created_at');

            // Index
            $table->index(['register_session_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};
