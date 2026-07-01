<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_stock_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_log_id')->constrained('shift_logs')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->decimal('opening_quantity', 12, 3);
            $table->decimal('closing_quantity_expected', 12, 3)->nullable();
            $table->decimal('closing_quantity_actual', 12, 3)->nullable();
            $table->decimal('variance', 12, 3)->nullable();
            $table->timestamps();

            $table->unique(['shift_log_id', 'ingredient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_stock_counts');
    }
};
