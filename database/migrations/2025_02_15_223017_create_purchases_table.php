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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->date('purchase_date');
            $table->string('party_code');
            $table->string('party_name');
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->longText('item')->nullable();
            $table->longText('rate')->nullable();
            $table->longText('product_mode')->nullable();
            $table->longText('pcs')->nullable();
            $table->longText('discount')->nullable();
            $table->longText('amount')->nullable();
            $table->longText('pcs_carton')->nullable();
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->tinyInteger('return_status')->default(0); // 0 = no return, 1 = returned
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
        Schema::dropIfExists('purchases');
    }
};
