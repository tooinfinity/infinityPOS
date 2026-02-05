<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transfers', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('from_warehouse_id')->constrained('warehouses');
            $table->foreignId('to_warehouse_id')->constrained('warehouses');
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('reference_no')->unique();
            $table->string('status');
            $table->text('note')->nullable();
            $table->date('transfer_date');

            $table->timestamps();

            $table->index('reference_no');
            $table->index(['from_warehouse_id', 'to_warehouse_id']);
            $table->index(['status', 'transfer_date']);
        });
    }
};
