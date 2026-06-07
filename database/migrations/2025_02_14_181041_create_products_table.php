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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_or_user_id')->constrained('users')->onDelete('cascade');
            $table->string('item_code')->unique();
            $table->string('item_name');
            $table->string('product_mode')->default('simple'); // simple or measurements
            $table->decimal('height', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('area', 10, 2)->nullable();
            $table->decimal('wholesale_price', 10, 2)->default(0);
            $table->decimal('retail_price', 10, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
