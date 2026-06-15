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
            if (!Schema::hasColumn('sales', 'return_status')) {
                $table->tinyInteger('return_status')->default(0)->after('grand_total');
            }
        });

        Schema::table('local_sales', function (Blueprint $table) {
            if (!Schema::hasColumn('local_sales', 'return_status')) {
                $table->tinyInteger('return_status')->default(0)->after('remaining_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('return_status');
        });

        Schema::table('local_sales', function (Blueprint $table) {
            $table->dropColumn('return_status');
        });
    }
};
