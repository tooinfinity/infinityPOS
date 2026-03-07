<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', static function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active');

            $table->timestamps();

            $table->index('name');
            $table->index('is_active');
        });
    }
};
