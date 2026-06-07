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
        Schema::table('stock_outs', function (Blueprint $table) {
            $table->foreignId('local_sales_id')->nullable()->constrained('local_sales')->onDelete('cascade')->after('product_id');
            $table->decimal('current_stock', 10, 2)->default(0)->after('local_sales_id');
            $table->decimal('close_stock', 10, 2)->default(0)->after('current_stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_outs', function (Blueprint $table) {
            $table->dropForeignIdFor('local_sales');
            $table->dropColumn('local_sales_id');
            $table->dropColumn('current_stock');
            $table->dropColumn('close_stock');
        });
    }
};
