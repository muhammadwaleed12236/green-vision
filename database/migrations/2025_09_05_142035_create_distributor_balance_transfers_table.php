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
        Schema::create('distributor_balance_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_distributor_id')->constrained('distributors')->onDelete('cascade');
            $table->unsignedBigInteger('to_distributor')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('transfer_date')->nullable();
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
        Schema::dropIfExists('distributor_balance_transfers');
    }
};
