<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_or_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('staff_id')->constrained('sales_mens')->onDelete('cascade');
            $table->date('attendance_date');
            $table->enum('status', ['pending', 'notified', 'dismissed'])->default('pending');
            $table->timestamp('dismissed_until')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Unique constraint - one notification per staff per date per admin
            $table->unique(['admin_or_user_id', 'staff_id', 'attendance_date'], 'att_notif_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_notifications');
    }
};
