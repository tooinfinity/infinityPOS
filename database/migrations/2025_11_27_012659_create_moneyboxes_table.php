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
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->boolean('is_active')->index();

            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->references('id')->on('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['type', 'is_active']);

            $table->index(['store_id', 'is_active']);
        });
    }
};
