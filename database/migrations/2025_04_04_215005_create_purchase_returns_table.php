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
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_id')->nullable();
            $table->unsignedBigInteger('party_name')->nullable(); // vendor_id
            $table->date('return_date')->nullable();
            $table->longText('item')->nullable();
            $table->longText('rate')->nullable();
            $table->longText('return_qty')->nullable();
            $table->longText('discount')->nullable();
            $table->longText('return_amount')->nullable();
            $table->decimal('total_return_amount', 15, 2)->default(0);
            $table->string('return_items')->nullable(); // summary text
            $table->string('reason')->nullable();
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
        Schema::dropIfExists('purchase_returns');
    }
};
