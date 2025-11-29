<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique()->comment('PRD-001, EXP-001');
            $table->enum('type', ['product', 'expense']);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }
};
