<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_identifiers', function (Blueprint $table): void {
            $table->id();
            $table->string('article')->unique()->nullable();
            $table->string('nif')->unique()->nullable();
            $table->string('nis')->unique()->nullable();
            $table->string('rc')->unique()->nullable();
            $table->string('rib')->unique()->nullable();

            $table->timestamps();
        });
    }
};
