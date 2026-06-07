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
            // Add vendor_id for vendor assignments
            $table->foreignId('vendor_id')->nullable()->after('staff_id')->constrained('vendors')->onDelete('cascade');

            // Add assignee_type to distinguish between inhouse, contractor, vendor
            $table->enum('assignee_type', ['inhouse', 'contractor', 'vendor'])->default('inhouse')->after('vendor_id');

            // Add staff_type column for backward compatibility
            $table->string('staff_type')->default('inhouse')->after('assignee_type');

            // Add expected_return_date for tracking when job should be completed
            $table->date('expected_return_date')->nullable()->after('staff_type');

            // Add notify_days_before for return date notifications
            $table->integer('notify_days_before')->default(2)->after('expected_return_date');

            // Add items_json to store items (replacement for work_type structure)
            $table->longText('items_json')->nullable()->after('notify_days_before');

            // Keep work_type for now for backward compatibility (don't drop yet)
            // We'll handle migration of existing data separately if needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropColumn(['vendor_id', 'assignee_type', 'staff_type', 'expected_return_date', 'notify_days_before', 'items_json']);
        });
    }
};
