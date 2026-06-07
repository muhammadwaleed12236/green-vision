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
        Schema::create('staff_attendences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('sales_mens')->onDelete('cascade');
            $table->foreignId('admin_or_user_id')->constrained('users')->onDelete('cascade');
            $table->date('attendence_date');
            $table->enum('status', ['present', 'absent', 'leave', 'half_day'])->nullable();
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->string('overtime_hours')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('marked_by_admin_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            // Unique index - one attendance record per staff per date per user
            $table->unique(['staff_id', 'attendence_date', 'admin_or_user_id'], 'staff_att_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_attendences');
    }
};
