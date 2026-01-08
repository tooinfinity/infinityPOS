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
        Schema::create('expenses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained('stores');
            $table->foreignId('register_session_id')->nullable()->constrained('register_sessions');
            $table->enum('expense_category', ['utilities', 'supplies', 'maintenance', 'other']);
            $table->integer('amount'); // in cents
            $table->text('description');
            $table->date('expense_date');
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
