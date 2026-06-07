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
        Schema::table('staff_recoveries', function (Blueprint $table) {
            $table->unsignedBigInteger('saleman_id')->nullable()->after('saleman_ledger_id');
            $table->date('date')->nullable()->after('recovery_date');
            $table->enum('adjust_type', ['plus', 'minus'])->default('plus')->after('amount');
            $table->decimal('adjust_amount', 15, 2)->default(0)->after('adjust_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_recoveries', function (Blueprint $table) {
            $table->dropColumn(['saleman_id', 'date', 'adjust_type', 'adjust_amount']);
        });
    }
};
