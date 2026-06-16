<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_outs', function (Blueprint $table) {
            $table->index('local_sales_id', 'idx_stock_outs_local_sales_id');
            $table->index('created_at', 'idx_stock_outs_created_at');
        });
    }

    public function down(): void
    {
        Schema::table('stock_outs', function (Blueprint $table) {
            $table->dropIndex('idx_stock_outs_local_sales_id');
            $table->dropIndex('idx_stock_outs_created_at');
        });
    }
};
