<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_returns', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('purchase_id')->constrained();
            $table->foreignId('warehouse_id')->constrained();
            $table->foreignId('user_id')->nullable()->constrained();

            $table->string('reference_no')->unique();
            $table->date('return_date');
            $table->unsignedBigInteger('total_amount');
            $table->string('status');
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index('reference_no');
            $table->index('purchase_id');
            $table->index('warehouse_id');
            $table->index('return_date');
        });
    }
};
