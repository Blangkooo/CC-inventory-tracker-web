<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PurgeStaleReceipts nulls out image_path once a receipt's photo is
     * purged past retention, keeping the row for audit history. That
     * requires the column to actually accept null.
     */
    public function up(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->string('image_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->string('image_path')->nullable(false)->change();
        });
    }
};
