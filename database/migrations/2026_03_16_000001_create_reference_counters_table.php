<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reference_counters', static function (Blueprint $table): void {
            $table->string('key', 30)->primary();
            $table->unsignedBigInteger('last_value')->default(0);
        });
    }
};
