<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moneyboxes', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->enum('type', ['cash_register', 'bank_account', 'mobile_money', 'other'])->default('cash_register');
            $table->text('description')->nullable();

            // Balance tracking
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);

            // Bank details (if type = bank_account)
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('iban')->nullable();

            // Assignment
            $table->foreignId('store_id')->nullable()->constrained();
            $table->foreignId('user_id')->nullable()->constrained();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['store_id', 'is_active']);
            $table->index(['type', 'is_active']);
        });
    }
};
