<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_registers', function (Blueprint $table): void {
            $table->timestamp('configured_at')->nullable()->after('draft_sale_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('pos_registers', function (Blueprint $table): void {
            $table->dropColumn('configured_at');
        });
    }
};
