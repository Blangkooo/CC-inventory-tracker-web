<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('birthday')->nullable();              // "March 15, 1998" or date
            $table->string('senior_high')->nullable();           // Senior high school name
            $table->string('college')->nullable();              // College/university
            $table->string('partner_contact')->nullable();       // Emergency contact: partner
            $table->string('mother_contact')->nullable();        // Emergency contact: mother
            $table->json('skills')->nullable();                 // ["Barista", "Chef"]
            $table->text('notes')->nullable();                  // Free-text notes
            $table->json('work_schedule')->nullable();          // {"Mon":"10:00 AM — 8:00 PM", ...}
            $table->json('performance_metrics')->nullable();    // ["Always on time", "Good service"]

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_profiles');
    }
};
