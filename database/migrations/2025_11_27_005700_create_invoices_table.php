<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->string('reference')->unique();

            $table->foreignId('sale_id')->constrained();
            $table->foreignId('client_id')->nullable()->constrained();

            $table->date('issued_at')->index();
            $table->date('due_at')->nullable();
            $table->date('paid_at')->nullable();

            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('paid', 15, 2)->default(0);

            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft')->index();

            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index(['due_at', 'status']);
        });
    }
};
