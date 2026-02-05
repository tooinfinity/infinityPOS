<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->string('batch_number')->nullable();
            $table->unsignedBigInteger('cost_amount');
            $table->unsignedInteger('quantity');
            $table->date('expires_at')->nullable();

            $table->timestamps();

            $table->index('product_id');
            $table->index(['product_id', 'expires_at']);
            $table->index(['expires_at', 'quantity']);
        });
    }
};
