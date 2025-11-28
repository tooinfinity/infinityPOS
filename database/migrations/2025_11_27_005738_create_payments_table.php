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
            $table->string('reference')->nullable();

            $table->morphs('payable'); // sales, purchases, invoices, returns

            $table->decimal('amount', 15, 2);
            $table->enum('method', ['cash', 'card', 'transfer'])->default('cash');

            $table->foreignId('moneybox_id')->nullable()->constrained();

            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->timestamps();

            $table->index(['payable_type', 'payable_id']);
            $table->index('reference');
            $table->index('method');
            $table->index('moneybox_id');
        });
    }
};
