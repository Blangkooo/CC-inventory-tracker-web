<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Owners can now add/remove payment categories from Settings, so the
     * fixed MySQL ENUM has to go — a plain string column plus AppSetting's
     * dynamic list (see PaymentsController::categories()) replaces it.
     * SQLite never enforced the ENUM as a real constraint, so there is
     * nothing to widen there; skip rather than error the migration run.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE payments MODIFY COLUMN category VARCHAR(50) NOT NULL DEFAULT 'other'");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        // Owner-added categories beyond the original widened set would
        // violate this ENUM on rollback; fold anything unrecognized into
        // 'other' first so the ALTER doesn't fail outright.
        $known = ['rent', 'utilities', 'supplier', 'salary', 'maintenance', 'packaging', 'utensils', 'gas', 'wages', 'other'];

        DB::table('payments')->whereNotIn('category', $known)->update(['category' => 'other']);

        $list = collect($known)->map(fn ($v) => "'".$v."'")->implode(',');
        DB::statement("ALTER TABLE payments MODIFY COLUMN category ENUM($list) NOT NULL DEFAULT 'other'");
    }
};
