<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('expense_category_id')->constrained();
            $table->foreignId('user_id')->nullable()->constrained();

            $table->string('reference_no')->unique();
            $table->unsignedBigInteger('amount');
            $table->date('expense_date');
            $table->text('description')->nullable();
            $table->string('document')->nullable();

            $table->timestamps();

            $table->index('reference_no');
            $table->index(['expense_category_id', 'expense_date']);
        });
    }
};
