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
            $table->foreignId('company_id')->nullable()->constrained();
            $table->foreignId('supplier_id')->nullable()->constrained();
            $table->foreignId('client_id')->nullable()->constrained();
            $table->string('article')->nullable();
            $table->string('nif')->nullable();
            $table->string('nis')->nullable();
            $table->string('rc')->nullable();
            $table->string('rib')->nullable();

            $table->timestamps();
        });
    }
};
