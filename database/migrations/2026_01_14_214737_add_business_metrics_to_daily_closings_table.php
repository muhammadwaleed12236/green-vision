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
        Schema::table('daily_closings', function (Blueprint $table) {
            // Job metrics
            $table->integer('total_jobs')->default(0)->after('vouchers_count');
            $table->decimal('total_job_amount', 15, 2)->default(0)->after('total_jobs');
            $table->integer('assigned_jobs')->default(0)->after('total_job_amount');

            // Payment metrics
            $table->decimal('contractor_payments', 15, 2)->default(0)->after('assigned_jobs');
            $table->decimal('vendor_payments', 15, 2)->default(0)->after('contractor_payments');
            $table->decimal('expense_payments', 15, 2)->default(0)->after('vendor_payments');
            $table->decimal('customer_recoveries', 15, 2)->default(0)->after('expense_payments');
            $table->decimal('staff_payments', 15, 2)->default(0)->after('customer_recoveries');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_closings', function (Blueprint $table) {
            $table->dropColumn([
                'total_jobs',
                'total_job_amount',
                'assigned_jobs',
                'contractor_payments',
                'vendor_payments',
                'expense_payments',
                'customer_recoveries',
                'staff_payments'
            ]);
        });
    }
};
