<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discrepancy_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->enum('type', ['stock_mismatch', 'shift_variance'])->default('stock_mismatch');
            $table->foreignId('ingredient_id')->nullable()->constrained('ingredients')->nullOnDelete();
            $table->foreignId('shift_log_id')->nullable()->constrained('shift_logs')->nullOnDelete();
            $table->decimal('expected_value', 12, 3)->nullable();
            $table->decimal('actual_value', 12, 3)->nullable();
            $table->decimal('variance', 12, 3)->nullable();
            $table->text('details')->nullable();
            $table->enum('status', ['pending', 'reviewed', 'dismissed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discrepancy_alerts');
    }
};
