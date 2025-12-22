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
            $table->string('reference')->nullable()->unique();
            $table->unsignedBigInteger('amount');
            $table->string('method', 20)->index();
            $table->text('notes')->nullable();
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();

            $table->foreignId('moneybox_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->references('id')->on('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['related_type', 'related_id'], 'payments_related_morph_index');
            $table->index(['related_type', 'related_id', 'created_at'], 'payments_related_type_related_id_created_at');
            $table->index(['method', 'created_at'], 'payments_method_created_at');

        });
    }
};
