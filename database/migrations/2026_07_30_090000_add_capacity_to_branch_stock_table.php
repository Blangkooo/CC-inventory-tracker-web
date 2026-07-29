<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branch_stock', function (Blueprint $table) {
            $table->decimal('capacity', 12, 3)->nullable()->after('min_threshold');
        });
    }

    public function down(): void
    {
        Schema::table('branch_stock', function (Blueprint $table) {
            $table->dropColumn('capacity');
        });
    }
};
