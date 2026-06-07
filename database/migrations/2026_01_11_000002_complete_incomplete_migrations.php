<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ==================== CUSTOMER RECOVERIES ====================
        Schema::table('customer_recoveries', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_recoveries', 'customer_ledger_id')) {
                $table->unsignedBigInteger('customer_ledger_id')->nullable();
                $table->decimal('amount', 15, 2)->default(0);
                $table->date('recovery_date')->nullable();
                $table->string('remarks')->nullable();
                $table->foreignId('admin_or_user_id')->constrained('users')->onDelete('cascade');
            }
        });

        // ==================== STAFF RECOVERIES ====================
        Schema::table('staff_recoveries', function (Blueprint $table) {
            if (!Schema::hasColumn('staff_recoveries', 'saleman_ledger_id')) {
                $table->unsignedBigInteger('saleman_ledger_id')->nullable();
                $table->decimal('amount', 15, 2)->default(0);
                $table->date('recovery_date')->nullable();
                $table->string('remarks')->nullable();
                $table->foreignId('admin_or_user_id')->constrained('users')->onDelete('cascade');
            }
        });

        // ==================== CONTRACTOR RECOVERIES ====================
        Schema::table('contractor_recoveries', function (Blueprint $table) {
            if (!Schema::hasColumn('contractor_recoveries', 'contractor_ledger_id')) {
                $table->unsignedBigInteger('contractor_ledger_id')->nullable();
                $table->decimal('amount', 15, 2)->default(0);
                $table->date('recovery_date')->nullable();
                $table->string('remarks')->nullable();
                $table->foreignId('admin_or_user_id')->constrained('users')->onDelete('cascade');
            }
        });

        // ==================== PURCHASE RETURNS ====================
        Schema::table('purchase_returns', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_returns', 'purchase_id')) {
                $table->unsignedBigInteger('purchase_id')->nullable();
                $table->string('return_items')->nullable();
                $table->decimal('return_amount', 15, 2)->default(0);
                $table->date('return_date')->nullable();
                $table->string('reason')->nullable();
                $table->foreignId('admin_or_user_id')->constrained('users')->onDelete('cascade');
            }
        });

        // ==================== SALE RETURNS ====================
        Schema::table('sale_returns', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_returns', 'party_id')) {
                $table->unsignedBigInteger('party_id')->nullable();
                $table->string('party_type')->nullable();
                $table->string('return_items')->nullable();
                $table->decimal('return_amount', 15, 2)->default(0);
                $table->date('return_date')->nullable();
                $table->string('reason')->nullable();
                $table->foreignId('admin_or_user_id')->constrained('users')->onDelete('cascade');
            }
        });

        // ==================== DISTRIBUTOR PRODUCTS ====================
        Schema::table('distributor_products', function (Blueprint $table) {
            if (!Schema::hasColumn('distributor_products', 'distributor_id')) {
                $table->foreignId('distributor_id')->constrained('distributors')->onDelete('cascade');
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->integer('quantity')->default(0);
                $table->decimal('rate', 10, 2)->default(0);
                $table->foreignId('admin_or_user_id')->constrained('users')->onDelete('cascade');
            }
        });

        // ==================== DISTRIBUTOR SALE RETURNS ====================
        Schema::table('distributor_sale_returns', function (Blueprint $table) {
            if (!Schema::hasColumn('distributor_sale_returns', 'sale_id')) {
                $table->unsignedBigInteger('sale_id')->nullable();
                $table->string('return_items')->nullable();
                $table->decimal('return_amount', 15, 2)->default(0);
                $table->date('return_date')->nullable();
                $table->string('reason')->nullable();
                $table->foreignId('admin_or_user_id')->constrained('users')->onDelete('cascade');
            }
        });

        // ==================== LOCAL SALES ====================
        Schema::table('local_sales', function (Blueprint $table) {
            if (!Schema::hasColumn('local_sales', 'invoice_number')) {
                $table->string('invoice_number')->unique();
                $table->date('sale_date')->nullable();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->unsignedBigInteger('vendor_id')->nullable();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->integer('quantity')->default(0);
                $table->decimal('rate', 15, 2)->default(0);
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->foreignId('admin_or_user_id')->constrained('users')->onDelete('cascade');
            }
        });

        // ==================== JOB ORDERS ====================
        Schema::table('job_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('job_orders', 'job_order_number')) {
                $table->string('job_order_number')->unique();
                $table->date('order_date')->nullable();
                $table->string('status')->default('pending');
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('job_orders', 'admin_or_user_id')) {
                $table->foreignId('admin_or_user_id')->constrained('users')->onDelete('cascade');
            }
        });

        // ==================== JOB ITEMS ====================
        Schema::table('job_items', function (Blueprint $table) {
            if (!Schema::hasColumn('job_items', 'job_order_id')) {
                $table->unsignedBigInteger('job_order_id')->nullable();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->integer('quantity')->default(0);
                $table->text('specifications')->nullable();
                $table->foreignId('admin_or_user_id')->constrained('users')->onDelete('cascade');
            }
        });

        // ==================== VENDOR BUILTIES ====================
        Schema::table('vendor_builties', function (Blueprint $table) {
            if (!Schema::hasColumn('vendor_builties', 'vendor_id')) {
                $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
                $table->string('builty_number')->unique();
                $table->date('builty_date')->nullable();
                $table->string('destination')->nullable();
                $table->text('remarks')->nullable();
                $table->foreignId('admin_or_user_id')->constrained('users')->onDelete('cascade');
            }
        });

        // ==================== VENDOR PAYMENTS ====================
        Schema::table('vendor_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('vendor_payments', 'vendor_id')) {
                $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
                $table->decimal('amount', 15, 2)->default(0);
                $table->date('payment_date')->nullable();
                $table->string('payment_method')->nullable();
                $table->string('reference')->nullable();
                $table->string('remarks')->nullable();
                $table->foreignId('admin_or_user_id')->constrained('users')->onDelete('cascade');
            }
        });

        // ==================== ADD EXPENSES ====================
        Schema::table('add_expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('add_expenses', 'expense_id')) {
                $table->foreignId('expense_id')->constrained('expenses')->onDelete('cascade');
                $table->decimal('amount', 15, 2)->default(0);
                $table->date('expense_date')->nullable();
                $table->string('description')->nullable();
                $table->foreignId('admin_or_user_id')->constrained('users')->onDelete('cascade');
            }
        });

        // ==================== CREATE BILLS ====================
        Schema::table('create_bills', function (Blueprint $table) {
            if (!Schema::hasColumn('create_bills', 'bill_number')) {
                $table->string('bill_number')->unique();
                $table->date('bill_date')->nullable();
                $table->string('party_type')->nullable();
                $table->unsignedBigInteger('party_id')->nullable();
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->string('status')->default('pending');
                $table->foreignId('admin_or_user_id')->constrained('users')->onDelete('cascade');
            }
        });

        // ==================== DISTRIBUTOR BALANCE TRANSFERS ====================
        Schema::table('distributor_balance_transfers', function (Blueprint $table) {
            if (!Schema::hasColumn('distributor_balance_transfers', 'from_distributor_id')) {
                $table->foreignId('from_distributor_id')->constrained('distributors')->onDelete('cascade');
                $table->unsignedBigInteger('to_distributor')->nullable();
                $table->decimal('amount', 15, 2)->default(0);
                $table->date('transfer_date')->nullable();
                $table->string('remarks')->nullable();
                $table->foreignId('admin_or_user_id')->constrained('users')->onDelete('cascade');
            }
        });

        // ==================== CONTRACTOR LEDGERS ====================
        Schema::table('contractor_ledgers', function (Blueprint $table) {
            if (!Schema::hasColumn('contractor_ledgers', 'contractor_id')) {
                $table->foreignId('contractor_id')->constrained('contractors')->onDelete('cascade');
                $table->decimal('previous_balance', 15, 2)->default(0);
                $table->decimal('closing_balance', 15, 2)->default(0);
                $table->foreignId('admin_or_user_id')->constrained('users')->onDelete('cascade');
            }
        });

        // ==================== CONTRACTORS ====================
        Schema::table('contractors', function (Blueprint $table) {
            if (!Schema::hasColumn('contractors', 'contractor_name')) {
                $table->string('contractor_name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('address')->nullable();
                $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('set null');
                $table->foreignId('area_id')->nullable()->constrained('areas')->onDelete('set null');
                $table->foreignId('admin_or_user_id')->constrained('users')->onDelete('cascade');
                $table->tinyInteger('status')->default(1);
            }
        });
    }

    public function down(): void
    {
        // Migrations won't be reversed - this is a data structure fix
    }
};
