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
        Schema::create('daily_closings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_or_user_id')->constrained('users')->onDelete('cascade');
            $table->date('closing_date')->index();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('total_receipts', 15, 2)->default(0);
            $table->decimal('total_payments', 15, 2)->default(0);
            $table->decimal('calculated_closing', 15, 2)->default(0);
            $table->decimal('actual_cash_in_hand', 15, 2)->default(0);
            $table->decimal('variance', 15, 2)->default(0); // Difference between calculated and actual
            $table->integer('vouchers_count')->default(0);
            $table->text('remarks')->nullable();
            $table->timestamp('closed_at');
            $table->timestamps();

            // Ensure one closing per user per date
            $table->unique(['admin_or_user_id', 'closing_date'], 'unique_user_closing_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_closings');
    }
};
