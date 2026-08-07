<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->enum('category', ['rent', 'utilities', 'supplier', 'salary', 'maintenance', 'other'])->default('other');
            $table->string('payee');
            $table->decimal('amount', 12, 2);
            $table->enum('method', ['cash', 'bank_transfer', 'gcash', 'check', 'other'])->default('cash');
            $table->enum('status', ['pending', 'paid', 'overdue'])->default('paid');
            $table->date('due_date')->nullable();
            $table->date('paid_at')->nullable();
            $table->string('receipt_photo')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
