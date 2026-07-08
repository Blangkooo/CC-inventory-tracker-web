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
        Schema::table('products', function (Blueprint $table) {
            $table->text('procedure')->nullable()->after('price');
        });

        Schema::table('recipes', function (Blueprint $table) {
            $table->enum('size', ['regular', 'large'])->default('regular')->after('ingredient_id');
        });

        Schema::table('recipes', function (Blueprint $table) {
            // Add the new composite unique (still leftmost-compatible with the
            // product_id FK) before dropping the old one, so InnoDB always has
            // a supporting index for the foreign key and doesn't refuse the drop.
            $table->unique(['product_id', 'ingredient_id', 'size']);
            $table->dropUnique(['product_id', 'ingredient_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->unique(['product_id', 'ingredient_id']);
            $table->dropUnique(['product_id', 'ingredient_id', 'size']);
            $table->dropColumn('size');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('procedure');
        });
    }
};
