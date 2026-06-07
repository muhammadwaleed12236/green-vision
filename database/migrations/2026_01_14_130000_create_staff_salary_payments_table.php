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
        Schema::create('staff_salary_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_or_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('staff_id')->constrained('sales_mens')->onDelete('cascade');
            $table->string('payment_month'); // Format: 2026-01
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('advance_deducted', 15, 2)->default(0);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->date('payment_date');
            $table->integer('days_present')->default(0);
            $table->integer('days_absent')->default(0);
            $table->string('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_salary_payments');
    }
};
