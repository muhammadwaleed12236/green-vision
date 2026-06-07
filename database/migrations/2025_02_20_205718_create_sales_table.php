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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->date('sale_date')->nullable();
            $table->foreignId('distributor_id')->nullable()->constrained('distributors')->onDelete('set null');
            $table->string('distributor_city')->nullable();
            $table->string('distributor_area')->nullable();
            $table->string('distributor_address')->nullable();
            $table->string('distributor_phone')->nullable();
            $table->string('Saleman')->nullable();
            $table->json('category')->nullable();
            $table->json('subcategory')->nullable();
            $table->json('code')->nullable();
            $table->json('item')->nullable();
            $table->json('size')->nullable();
            $table->json('pcs_carton')->nullable();
            $table->json('carton_qty')->nullable();
            $table->json('pcs_qty')->nullable();
            $table->json('liter')->nullable();
            $table->json('rate')->nullable();
            $table->json('discount')->nullable();
            $table->json('amount')->nullable();
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->unsignedBigInteger('sale_id')->nullable();
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
        Schema::dropIfExists('sales');
    }
};
