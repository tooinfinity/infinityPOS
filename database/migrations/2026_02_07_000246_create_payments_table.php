<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_method_id')->constrained();
            $table->foreignId('user_id')->nullable()->constrained();

            $table->string('reference_no')->unique();
            $table->string('payable_type');
            $table->unsignedBigInteger('payable_id');
            $table->unsignedBigInteger('amount');
            $table->date('payment_date');
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['payable_type', 'payable_id']);
            $table->index('reference_no');
            $table->index('payment_date');
        });
    }
};
