<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->index();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('article')->unique()->nullable();
            $table->string('nif')->unique()->nullable();
            $table->string('nis')->unique()->nullable();
            $table->string('rc')->unique()->nullable();
            $table->string('rib')->unique()->nullable();
            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->references('id')->on('users')->nullOnDelete();

            $table->timestamps();
        });
    }
};
