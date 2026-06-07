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
        Schema::table('job_orders', function (Blueprint $table) {
            // Add financial columns
            $table->decimal('total_amount', 15, 2)->default(0)->after('notify_days_before');
            $table->decimal('paid_amount', 15, 2)->default(0)->after('total_amount');
            $table->decimal('remaining_amount', 15, 2)->default(0)->after('paid_amount');

            // Add work_type for backward compatibility with old job orders
            $table->longText('work_type')->nullable()->after('items_json');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            $table->dropColumn(['total_amount', 'paid_amount', 'remaining_amount', 'work_type']);
        });
    }
};
