<?php

namespace App\Http\Controllers;

use App\Models\LocalSale;
use App\Models\JobOrder;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\Contractor;
use App\Models\Salesman;
use App\Models\AddExpense;
use App\Models\Expense;
use App\Models\JournalVoucher;
use App\Models\CustomerLedger;
use App\Models\VendorLedger;
use App\Models\ContractorLedger;
use App\Models\StaffAdvance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BusinessReportController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        // Date filters
        $fromDate = $request->from_date ?? now()->startOfMonth()->format('Y-m-d');
        $toDate = $request->to_date ?? now()->format('Y-m-d');

        // ==================== TOP SUMMARY BOXES ====================

        // 1. Total Jobs (from local_sales - invoices)
        $totalJobs = LocalSale::where('admin_or_user_id', $userId)
            ->whereDate('sale_date', '>=', $fromDate)
            ->whereDate('sale_date', '<=', $toDate)
            ->count();

        $totalJobsAmount = LocalSale::where('admin_or_user_id', $userId)
            ->whereDate('sale_date', '>=', $fromDate)
            ->whereDate('sale_date', '<=', $toDate)
            ->sum('net_amount'); // FIXED: Use net_amount (after discount) instead of grand_total

        $totalJobsReceivedAdvance = LocalSale::where('admin_or_user_id', $userId)
            ->whereDate('sale_date', '>=', $fromDate)
            ->whereDate('sale_date', '<=', $toDate)
            ->sum('advance_amount');

        $totalCustomerRecoveriesData = \App\Models\CustomerRecovery::join('customer_ledgers', 'customer_recoveries.customer_ledger_id', '=', 'customer_ledgers.id')
            ->where('customer_ledgers.admin_or_user_id', $userId)
            ->whereDate('customer_recoveries.date', '>=', $fromDate)
            ->whereDate('customer_recoveries.date', '<=', $toDate)
            ->sum('customer_recoveries.amount_paid');

        $totalCustomerReceiptsData = JournalVoucher::where('admin_or_user_id', $userId)
            ->where('voucher_type', 'receipt')
            ->where('party_type', 'customer')
            ->whereDate('voucher_date', '>=', $fromDate)
            ->whereDate('voucher_date', '<=', $toDate)
            ->sum('credit_amount');

        $totalJobsReceived = $totalJobsReceivedAdvance + $totalCustomerRecoveriesData + $totalCustomerReceiptsData;

        // Job Pending = Total Jobs Amount - Total Received (in this period). Prevent negative.
        $totalJobsPending = $totalJobsAmount - $totalJobsReceived;
        if ($totalJobsPending < 0) {
            $totalJobsPending = 0;
        }

        // 2. Job Assignment Expenses (FIXED: Only from AddExpense table to avoid double counting)
        $jobAssignmentExpense = AddExpense::where('add_expenses.admin_or_user_id', $userId)
            ->join('expenses', 'add_expenses.expense_id', '=', 'expenses.id')
            ->whereIn('expenses.expense_name', ['Job Assignment - Contractor', 'Job Assignment - Vendor'])
            ->whereDate('add_expenses.expense_date', '>=', $fromDate)
            ->whereDate('add_expenses.expense_date', '<=', $toDate)
            ->sum('add_expenses.amount');

        // 3. Other Expenses (Tea, Coffee, General expenses) - Exclude job assignment expenses
        $otherExpenses = AddExpense::where('add_expenses.admin_or_user_id', $userId)
            ->join('expenses', 'add_expenses.expense_id', '=', 'expenses.id')
            ->whereNotIn('expenses.expense_name', ['Job Assignment - Contractor', 'Job Assignment - Vendor'])
            ->whereDate('add_expenses.expense_date', '>=', $fromDate)
            ->whereDate('add_expenses.expense_date', '<=', $toDate)
            ->sum('add_expenses.amount');

        // Note: Journal vouchers for expenses automatically create an AddExpense record.
        // Therefore, we don't need to add expense vouchers manually here, otherwise it doubles.
        $totalOtherExpenses = $otherExpenses;

        // 4. Staff Payments
        $staffPayments = JournalVoucher::where('admin_or_user_id', $userId)
            ->where('voucher_type', 'payment')
            ->where('party_type', 'staff')
            ->whereDate('voucher_date', '>=', $fromDate)
            ->whereDate('voucher_date', '<=', $toDate)
            ->sum('debit_amount');

        // 5. Total Payments Out (Includes vendor, contractor, staff, expense etc to match Journal Vouchers)
        $totalPaymentsOutVouchers = JournalVoucher::where('admin_or_user_id', $userId)
            ->where('voucher_type', 'payment')
            ->whereDate('voucher_date', '>=', $fromDate)
            ->whereDate('voucher_date', '<=', $toDate)
            ->sum('debit_amount');

        $totalPaymentsOut = $totalPaymentsOutVouchers;

        // 6. Total Receipts In
        $totalReceiptsInVouchers = JournalVoucher::where('admin_or_user_id', $userId)
            ->where('voucher_type', 'receipt')
            ->whereDate('voucher_date', '>=', $fromDate)
            ->whereDate('voucher_date', '<=', $toDate)
            ->sum('credit_amount');

        $totalCustomerRecoveries = \App\Models\CustomerRecovery::join('customer_ledgers', 'customer_recoveries.customer_ledger_id', '=', 'customer_ledgers.id')
            ->where('customer_ledgers.admin_or_user_id', $userId)
            ->whereDate('customer_recoveries.date', '>=', $fromDate)
            ->whereDate('customer_recoveries.date', '<=', $toDate)
            ->sum('customer_recoveries.amount_paid');

        $totalReceiptsIn = $totalReceiptsInVouchers + $totalCustomerRecoveries;

        // 7. Vendor Payments (New)
        $vendorPayments = JournalVoucher::where('admin_or_user_id', $userId)
            ->where('voucher_type', 'payment')
            ->where('party_type', 'vendor')
            ->whereDate('voucher_date', '>=', $fromDate)
            ->whereDate('voucher_date', '<=', $toDate)
            ->sum('debit_amount');

        // 8. Contractor Payments (New)
        $contractorPayments = JournalVoucher::where('admin_or_user_id', $userId)
            ->where('voucher_type', 'payment')
            ->where('party_type', 'contractor')
            ->whereDate('voucher_date', '>=', $fromDate)
            ->whereDate('voucher_date', '<=', $toDate)
            ->sum('debit_amount');

        // 9. Net Profit = Job Amount - Job Assignment Expense - Other Expenses - Staff Payments
        $netProfit = $totalJobsAmount - $jobAssignmentExpense - $totalOtherExpenses - $staffPayments;

        // ==================== DUES & BALANCES ====================

        // Customer Dues (Total Remaining) - FIXED: Get latest balance for each customer
        $customerDues = CustomerLedger::where('admin_or_user_id', $userId)
            ->whereIn('id', function($query) use ($userId) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('customer_ledgers')
                    ->where('admin_or_user_id', $userId)
                    ->groupBy('customer_id');
            })
            ->sum('closing_balance');

        // Backup: Sum remaining from local_sales - ONLY for customers, not vendors
        $customerDuesFromSales = LocalSale::where('admin_or_user_id', $userId)
            ->where('party_type', 'customer') // Only customer sales
            ->whereNotNull('customer_id') // Must have customer_id
            ->sum('remaining_amount');

        // Vendor Dues (We owe them)
        $vendorDues = VendorLedger::where('admin_or_user_id', $userId)
            ->select('vendor_id', DB::raw('MAX(id) as max_id'))
            ->groupBy('vendor_id')
            ->get()
            ->sum(function($item) use ($userId) {
                $ledger = VendorLedger::find($item->max_id);
                return $ledger ? $ledger->closing_balance : 0;
            });

        // Contractor Dues
        $contractorDues = ContractorLedger::where('admin_or_user_id', $userId)
            ->select('contractor_id', DB::raw('MAX(id) as max_id'))
            ->groupBy('contractor_id')
            ->get()
            ->sum(function($item) use ($userId) {
                $ledger = ContractorLedger::find($item->max_id);
                return $ledger ? $ledger->closing_balance : 0;
            });

        // Staff Advances (Pending recovery)
        $staffAdvances = StaffAdvance::where('admin_or_user_id', $userId)
            ->where('status', '!=', 'recovered')
            ->sum('remaining_amount');

        // Total Customers
        $totalCustomers = Customer::where('admin_or_user_id', $userId)->count();

        // Total Vendors
        $totalVendors = Vendor::where('admin_or_user_id', $userId)->count();

        // Total Staff
        $totalStaff = Salesman::where('admin_or_user_id', $userId)->where('status', 1)->count();

        // Total Contractors
        $totalContractors = Contractor::where('admin_or_user_id', $userId)->count();

        // ==================== DATE-WISE BREAKDOWN ====================

        $dateWiseData = [];
        $currentDate = Carbon::parse($fromDate);
        $endDate = Carbon::parse($toDate);

        while ($currentDate <= $endDate) {
            $date = $currentDate->format('Y-m-d');

            // Jobs for this date
            $dayJobs = LocalSale::where('admin_or_user_id', $userId)
                ->whereDate('sale_date', $date)
                ->count();

            $dayJobsAmount = LocalSale::where('admin_or_user_id', $userId)
                ->whereDate('sale_date', $date)
                ->sum('net_amount'); // FIXED: Use net_amount (after discount)

            // Job Assignment (Vendor/Contractor) for this date - FIXED: Only from AddExpense
            $dayJobExpense = AddExpense::where('add_expenses.admin_or_user_id', $userId)
                ->join('expenses', 'add_expenses.expense_id', '=', 'expenses.id')
                ->whereIn('expenses.expense_name', ['Job Assignment - Contractor', 'Job Assignment - Vendor'])
                ->whereDate('add_expenses.expense_date', $date)
                ->sum('add_expenses.amount');

            // Other Expenses for this date (excluding job assignment expenses)
            $dayOtherExpense = AddExpense::where('add_expenses.admin_or_user_id', $userId)
                ->join('expenses', 'add_expenses.expense_id', '=', 'expenses.id')
                ->whereNotIn('expenses.expense_name', ['Job Assignment - Contractor', 'Job Assignment - Vendor'])
                ->whereDate('add_expenses.expense_date', $date)
                ->sum('add_expenses.amount');

            // Journal Voucher AddExpense record creates automatic duplicates if we add this manually, so leaving it.
            $dayExpenseVoucher = 0;

            // Staff Payment for this date
            $dayStaffPayment = JournalVoucher::where('admin_or_user_id', $userId)
                ->where('voucher_type', 'payment')
                ->where('party_type', 'staff')
                ->whereDate('voucher_date', $date)
                ->sum('debit_amount');

            // Customer Receipt for this date
            $dayCustomerReceiptVoucher = JournalVoucher::where('admin_or_user_id', $userId)
                ->where('voucher_type', 'receipt')
                ->where('party_type', 'customer')
                ->whereDate('voucher_date', $date)
                ->sum('credit_amount');

            $dayCustomerRecovery = \App\Models\CustomerRecovery::join('customer_ledgers', 'customer_recoveries.customer_ledger_id', '=', 'customer_ledgers.id')
                ->where('customer_ledgers.admin_or_user_id', $userId)
                ->whereDate('customer_recoveries.date', $date)
                ->sum('customer_recoveries.amount_paid');

            $dayCustomerReceipt = $dayCustomerReceiptVoucher + $dayCustomerRecovery;

            // Vendor Payment for this date
            $dayVendorPayment = JournalVoucher::where('admin_or_user_id', $userId)
                ->where('voucher_type', 'payment')
                ->where('party_type', 'vendor')
                ->whereDate('voucher_date', $date)
                ->sum('debit_amount');

            // Contractor Payment for this date
            $dayContractorPayment = JournalVoucher::where('admin_or_user_id', $userId)
                ->where('voucher_type', 'payment')
                ->where('party_type', 'contractor')
                ->whereDate('voucher_date', $date)
                ->sum('debit_amount');

            // Day Profit
            $dayProfit = $dayJobsAmount - $dayJobExpense - $dayOtherExpense - $dayStaffPayment;

            // Only add if there's any activity
            if ($dayJobs > 0 || $dayJobsAmount > 0 || $dayJobExpense > 0 || $dayOtherExpense > 0 || $dayStaffPayment > 0 || $dayCustomerReceipt > 0 || $dayVendorPayment > 0 || $dayContractorPayment > 0) {
                $dateWiseData[] = [
                    'date' => $date,
                    'formatted_date' => $currentDate->format('d M Y'),
                    'day_name' => $currentDate->format('l'),
                    'jobs_count' => $dayJobs,
                    'jobs_amount' => $dayJobsAmount,
                    'job_expense' => $dayJobExpense,
                    'other_expense' => $dayOtherExpense,
                    'staff_payment' => $dayStaffPayment,
                    'customer_receipt' => $dayCustomerReceipt,
                    'vendor_payment' => $dayVendorPayment,
                    'contractor_payment' => $dayContractorPayment,
                    'profit' => $dayProfit,
                ];
            }

            $currentDate->addDay();
        }

        // Reverse to show latest first
        $dateWiseData = array_reverse($dateWiseData);

        // Summary Stats
        $summaryStats = [
            'total_jobs' => $totalJobs,
            'total_jobs_amount' => $totalJobsAmount,
            'total_jobs_received' => $totalJobsReceived,
            'total_jobs_pending' => $totalJobsPending,
            'job_assignment_expense' => $jobAssignmentExpense,
            'other_expenses' => $totalOtherExpenses,
            'staff_payments' => $staffPayments,
            'total_payments_out' => $totalPaymentsOut,
            'total_receipts_in' => $totalReceiptsIn,
            'vendor_payments' => $vendorPayments,
            'contractor_payments' => $contractorPayments,
            'net_profit' => $netProfit,
        ];

        $duesStats = [
            'customer_dues' => $customerDues, // Use the fixed CustomerLedger calculation
            'vendor_dues' => $vendorDues,
            'contractor_dues' => $contractorDues,
            'staff_advances' => $staffAdvances,
            'total_customers' => $totalCustomers,
            'total_vendors' => $totalVendors,
            'total_staff' => $totalStaff,
            'total_contractors' => $totalContractors,
        ];

        return view('admin_panel.business_report.index', compact(
            'summaryStats', 'duesStats', 'dateWiseData', 'fromDate', 'toDate'
        ));
    }
}
