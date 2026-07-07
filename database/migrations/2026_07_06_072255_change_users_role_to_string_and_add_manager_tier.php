<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default(User::ROLE_STAFF)->change();
        });

        DB::table('users')->where('role', 'owner')->update(['role' => User::ROLE_SUPER_ADMIN]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')->where('role', User::ROLE_SUPER_ADMIN)->update(['role' => 'owner']);
        DB::table('users')->where('role', User::ROLE_MANAGER)->update(['role' => User::ROLE_STAFF]);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['owner', 'staff'])->default('staff')->change();
        });
    }
};
