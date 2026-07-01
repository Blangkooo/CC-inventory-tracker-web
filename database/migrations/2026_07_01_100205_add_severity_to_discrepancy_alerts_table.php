<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('discrepancy_alerts', function (Blueprint $table) {
            $table->enum('severity', ['low', 'medium', 'high'])->default('medium')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discrepancy_alerts', function (Blueprint $table) {
            $table->dropColumn('severity');
        });
    }
};
