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
        Schema::table('cash_books', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id')->nullable()->after('admin_or_user_id');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('set null');
        });

        Schema::table('journal_vouchers', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id')->nullable()->after('admin_or_user_id');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_books', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
        });

        Schema::table('journal_vouchers', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
        });
    }
};
