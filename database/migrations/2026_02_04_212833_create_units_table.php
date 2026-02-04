<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', static function (Blueprint $table): void {
            $table->id();
            $table->string('name'); // e.g., 'Piece', 'Kilogram', 'Liter'
            $table->string('short_name'); // e.g., 'pc', 'kg', 'l'
            $table->boolean('is_active');

            $table->timestamps();

            $table->unique(['name', 'short_name']);
            $table->index('is_active');
        });
    }
};
