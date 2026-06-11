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
        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_salesman_id')->nullable()->after('Saleman');
            $table->foreign('assigned_salesman_id')->references('id')->on('sales_mens')->onDelete('set null');
            $table->tinyInteger('cancel_status')->default(0)->after('assigned_salesman_id'); // 0=active, 1=cancelled
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['assigned_salesman_id']);
            $table->dropColumn(['assigned_salesman_id', 'cancel_status']);
        });
    }
};
