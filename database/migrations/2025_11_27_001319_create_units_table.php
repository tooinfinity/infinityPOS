<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->comment('kg, piece, meter, liter, etc');
            $table->string('short_name', 10)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }
};
