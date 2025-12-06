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
            $table->date('issued_at')->index();
            $table->date('due_at')->nullable();
            $table->date('paid_at')->nullable();
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('discount');
            $table->unsignedBigInteger('tax');
            $table->unsignedBigInteger('total');
            $table->unsignedBigInteger('paid');
            $table->string('status', 20)->index();
            $table->text('notes')->nullable();

            $table->foreignId('sale_id')->constrained();
            $table->foreignId('client_id')->nullable()->constrained();
            $table->foreignId('created_by')->references('id')->on('users');
            $table->foreignId('updated_by')->nullable()->references('id')->on('users');

            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index(['due_at', 'status']);
        });
    }
};
