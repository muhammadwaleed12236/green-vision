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
        Schema::create('staff_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_or_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('staff_id')->constrained('sales_mens')->onDelete('cascade');
            $table->enum('advance_type', ['salary', 'additional'])->default('salary');
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('recovered_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->default(0);
            $table->date('date');
            $table->string('remarks')->nullable();
            $table->enum('status', ['pending', 'partially_paid', 'cleared'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_advances');
    }
};
