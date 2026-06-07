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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('Party_code')->unique();
            $table->string('Party_name')->nullable();
            $table->string('Party_phone')->nullable();
            $table->string('Party_address')->nullable();
            $table->string('city')->nullable();
            $table->string('area')->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->string('recape_type')->nullable();
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
        Schema::dropIfExists('vendors');
    }
};
