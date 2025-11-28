<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_returns', function (Blueprint $table): void {
            $table->id();
            $table->string('reference')->unique();

            $table->foreignId('purchase_id')->nullable()->constrained();
            $table->foreignId('supplier_id')->nullable()->constrained();
            $table->foreignId('store_id')->constrained();

            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('refunded', 15, 2)->default(0);

            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed')->index();

            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->timestamps();
        });
    }
};
