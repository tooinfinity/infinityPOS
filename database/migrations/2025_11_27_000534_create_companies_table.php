<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_secondary')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('country')->nullable();

            $table->string('logo')->nullable();

            $table->string('website')->nullable();
            $table->text('description')->nullable();

            $table->string('currency', 3)->default('DZD')->comment('ISO currency code');
            $table->string('currency_symbol', 10)->default('د.ج');
            $table->string('timezone')->default('Africa/Algiers');
            $table->string('date_format')->default('Y-m-d');

            $table->foreignId('business_identifier_id')->nullable()->constrained();

            $table->timestamps();
        });
    }
};
