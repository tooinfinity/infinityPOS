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
            $table->string('name')->unique();
            $table->string('type', 20); // ['cash', 'bank', 'mobile']
            $table->text('description')->nullable();
            $table->decimal('balance', 15, 2)->default(0)->comment('Current balance');
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->boolean('is_active')->default(true)->index();

            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->references('id')->on('users');
            $table->foreignId('updated_by')->nullable()->references('id')->on('users');

            $table->timestamps();

            $table->index(['type', 'is_active']);
        });
    }
};
