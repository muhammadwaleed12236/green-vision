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
        Schema::table('sale_returns', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_returns', 'sale_type')) {
                $table->string('sale_type')->nullable();
            }
            if (!Schema::hasColumn('sale_returns', 'invoice_number')) {
                $table->string('invoice_number')->nullable();
            }
            if (!Schema::hasColumn('sale_returns', 'item_ids')) {
                $table->text('item_ids')->nullable();
            }
            if (!Schema::hasColumn('sale_returns', 'item_names')) {
                $table->text('item_names')->nullable();
            }
            if (!Schema::hasColumn('sale_returns', 'pcs_per_carton')) {
                $table->text('pcs_per_carton')->nullable();
            }
            if (!Schema::hasColumn('sale_returns', 'carton_qty')) {
                $table->text('carton_qty')->nullable();
            }
            if (!Schema::hasColumn('sale_returns', 'pcs_qty')) {
                $table->text('pcs_qty')->nullable();
            }
            if (!Schema::hasColumn('sale_returns', 'rate')) {
                $table->text('rate')->nullable();
            }
            if (!Schema::hasColumn('sale_returns', 'discount')) {
                $table->text('discount')->nullable();
            }
            if (!Schema::hasColumn('sale_returns', 'total')) {
                $table->text('total')->nullable();
            }
            if (!Schema::hasColumn('sale_returns', 'total_return_amount')) {
                $table->decimal('total_return_amount', 15, 2)->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_returns', function (Blueprint $table) {
            $table->dropColumn([
                'sale_type',
                'invoice_number',
                'item_ids',
                'item_names',
                'pcs_per_carton',
                'carton_qty',
                'pcs_qty',
                'rate',
                'discount',
                'total',
                'total_return_amount',
            ]);
        });
    }
};
