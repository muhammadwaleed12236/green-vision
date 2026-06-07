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
        Schema::table('staff_ledgers', function (Blueprint $table) {
            $table->date('week_start')->nullable()->after('staff_id');
            $table->date('week_end')->nullable()->after('week_start');
            $table->decimal('weekly_amount', 10, 2)->default(0)->after('week_end');
            $table->integer('days_present')->default(0)->after('weekly_amount');
            $table->integer('days_absent')->default(0)->after('days_present');
            $table->decimal('advance', 10, 2)->default(0)->after('days_absent');
            $table->decimal('paid', 10, 2)->default(0)->after('advance');
            $table->decimal('balance', 15, 2)->default(0)->after('paid');
            $table->text('note')->nullable()->after('balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_ledgers', function (Blueprint $table) {
            $table->dropColumn([
                'week_start',
                'week_end',
                'weekly_amount',
                'days_present',
                'days_absent',
                'advance',
                'paid',
                'balance',
                'note'
            ]);
        });
    }
};
