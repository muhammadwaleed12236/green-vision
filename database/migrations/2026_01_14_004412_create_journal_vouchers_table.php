<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_vouchers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_or_user_id');
            $table->string('voucher_no')->unique();
            $table->date('voucher_date');
            $table->enum('voucher_type', ['payment', 'receipt', 'journal'])->default('payment');

            // Party details
            $table->enum('party_type', ['vendor', 'customer', 'contractor', 'staff', 'expense', 'bank', 'cash', 'other'])->default('other');
            $table->unsignedBigInteger('party_id')->nullable();
            $table->string('party_name')->nullable(); // For manual entry or display

            // Account details
            $table->string('account_head')->nullable(); // Like: Salary, Purchase, Sale, Expense etc.

            // Amounts
            $table->decimal('debit_amount', 15, 2)->default(0);
            $table->decimal('credit_amount', 15, 2)->default(0);

            // Payment method
            $table->enum('payment_method', ['cash', 'bank', 'cheque', 'online'])->default('cash');
            $table->string('bank_name')->nullable();
            $table->string('cheque_no')->nullable();
            $table->date('cheque_date')->nullable();

            // Reference to original transaction
            $table->string('reference_type')->nullable(); // purchase, sale, expense, salary etc.
            $table->unsignedBigInteger('reference_id')->nullable();

            // Description
            $table->text('narration')->nullable();
            $table->text('remarks')->nullable();

            // Status
            $table->enum('status', ['pending', 'approved', 'cancelled'])->default('approved');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['admin_or_user_id', 'voucher_date']);
            $table->index(['party_type', 'party_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_vouchers');
    }
};
