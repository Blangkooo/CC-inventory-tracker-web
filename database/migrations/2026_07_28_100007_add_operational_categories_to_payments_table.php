<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Widen the payment category list to cover day-to-day operational costs
     * (packaging, utensils, gas, wages) alongside the existing overheads.
     *
     * Laravel's schema builder can't alter an enum in place, so both
     * directions are raw MODIFY statements — MySQL-only syntax. SQLite
     * (used by the test suite) never enforced the enum as a real constraint
     * in the first place, so there is nothing to widen there; skip rather
     * than error the whole migration run.
     */
    private const NEW = ['rent', 'utilities', 'supplier', 'salary', 'maintenance', 'packaging', 'utensils', 'gas', 'wages', 'other'];

    private const OLD = ['rent', 'utilities', 'supplier', 'salary', 'maintenance', 'other'];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement($this->modifyTo(self::NEW));
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        // Fold the retired categories into 'other' so no row violates the
        // narrower enum when it is put back.
        DB::table('payments')
            ->whereIn('category', ['packaging', 'utensils', 'gas', 'wages'])
            ->update(['category' => 'other']);

        DB::statement($this->modifyTo(self::OLD));
    }

    private function modifyTo(array $values): string
    {
        $list = collect($values)->map(fn ($v) => "'".$v."'")->implode(',');

        return "ALTER TABLE payments MODIFY COLUMN category ENUM($list) NOT NULL DEFAULT 'other'";
    }
};
