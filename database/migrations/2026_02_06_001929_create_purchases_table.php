<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_id')->constrained();
            $table->foreignId('warehouse_id')->constrained();
            $table->foreignId('user_id')->nullable()->constrained();

            $table->string('reference_no')->unique();
            $table->string('status');
            $table->date('purchase_date');
            $table->unsignedBigInteger('total_amount');
            $table->unsignedBigInteger('paid_amount');
            $table->string('payment_status');
            $table->text('note')->nullable();
            $table->string('document')->nullable();

            $table->timestamps();

            $table->index('reference_no');
            $table->index(['supplier_id', 'purchase_date']);
            $table->index(['status', 'payment_status']);
            $table->index('warehouse_id');
        });
    }
};
