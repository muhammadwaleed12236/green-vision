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
            // Check if column exists, then change. Ideally we assume it exists or we add it.
            // Since we are fixing it, let's use change().
             if (Schema::hasColumn('job_orders', 'staff_id')) {
                $table->unsignedBigInteger('staff_id')->nullable()->change();
            } else {
                $table->unsignedBigInteger('staff_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting not strictly required for this fix context, but good practice.
        // Determining original state is hard, assume nullable is fine.
    }
};
