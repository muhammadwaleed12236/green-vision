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
        Schema::create('customer_recoveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_ledger_id')->nullable();
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->string('salesman')->nullable();
            $table->date('date')->nullable();
            $table->string('remarks')->nullable();
            $table->foreignId('admin_or_user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_recoveries');
    }
};
