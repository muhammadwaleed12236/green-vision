<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_salary_payments', function (Blueprint $table) {
            // Add date range columns
            $table->date('from_date')->nullable()->after('payment_month');
            $table->date('to_date')->nullable()->after('from_date');
            $table->decimal('additional_advance_deducted', 15, 2)->default(0)->after('advance_deducted');
        });
    }

    public function down(): void
    {
        Schema::table('staff_salary_payments', function (Blueprint $table) {
            $table->dropColumn(['from_date', 'to_date', 'additional_advance_deducted']);
        });
    }
};
