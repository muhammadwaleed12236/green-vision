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
        Schema::table('local_sales', function (Blueprint $table) {
            $table->longText('split_payments')->nullable()->after('account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('local_sales', function (Blueprint $table) {
            $table->dropColumn('split_payments');
        });
    }
};
