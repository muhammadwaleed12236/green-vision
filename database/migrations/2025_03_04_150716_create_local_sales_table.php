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
        Schema::create('local_sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->date('sale_date')->nullable();

            // Party/Customer details
            $table->string('party_type')->nullable(); // customer, vendor, walkin
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_shopname')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_address')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();

            // Items (JSON arrays)
            $table->longText('item')->nullable();
            $table->longText('height')->nullable();
            $table->longText('width')->nullable();
            $table->longText('unit')->nullable();
            $table->longText('qty')->nullable();
            $table->longText('amount')->nullable();

            // Amounts
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2)->default(0);
            $table->decimal('advance_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->default(0);

            // Order status and delivery
            $table->string('job_status')->default('pending');
            $table->date('delivery_date')->nullable();
            $table->integer('notify_days_before')->default(2); // Notify X days before delivery

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
        Schema::dropIfExists('local_sales');
    }
};
