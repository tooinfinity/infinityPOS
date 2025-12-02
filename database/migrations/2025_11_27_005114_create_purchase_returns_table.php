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
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('refunded', 15, 2)->default(0);
            $table->string('status', 20)->index();
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('purchase_id')->nullable()->constrained();
            $table->foreignId('supplier_id')->nullable()->constrained();
            $table->foreignId('store_id')->constrained();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained();

            $table->timestamps();
        });
    }
};
