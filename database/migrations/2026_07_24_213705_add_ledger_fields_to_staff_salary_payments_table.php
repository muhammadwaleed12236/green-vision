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
        Schema::table('staff_salary_payments', function (Blueprint $table) {
            $table->string('pay_basis')->nullable();
            $table->decimal('daily_rate', 10, 2)->nullable();
            $table->decimal('days_paid', 8, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_salary_payments', function (Blueprint $table) {
            $table->dropColumn(['pay_basis', 'daily_rate', 'days_paid']);
        });
    }
};
