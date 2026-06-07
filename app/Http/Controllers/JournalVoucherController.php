<?php

namespace App\Http\Controllers;

use App\Models\JournalVoucher;
use App\Models\JobOrder;
use App\Models\LocalSale;
use App\Models\Vendor;
use App\Models\Customer;
use App\Models\Contractor;
use App\Models\Salesman;
use App\Models\Expense;
use App\Models\VendorLedger;
use App\Models\CustomerLedger;
use App\Models\ContractorLedger;
use App\Models\StaffAdvance;
use App\Models\AddExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JournalVoucherController extends Controller
{
    // Main Index - All Vouchers
    public function index(Request $request)
    {
        $query = JournalVoucher::where('admin_or_user_id', Auth::id());

        // Default: This month's data
        $fromDate = $request->from_date ?? now()->startOfMonth()->format('Y-m-d');
        $toDate = $request->to_date ?? now()->format('Y-m-d');

        $query->whereDate('voucher_date', '>=', $fromDate)
              ->whereDate('voucher_date', '<=', $toDate);

        // Filters
        if ($request->voucher_type) {
            $query->where('voucher_type', $request->voucher_type);
        }
        if ($request->party_type) {
            $query->where('party_type', $request->party_type);
        }
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('voucher_no', 'like', "%{$search}%")
                  ->orWhere('party_name', 'like', "%{$search}%")
                  ->orWhere('narration', 'like', "%{$search}%");
            });
        }

        // Calculate filtered totals BEFORE pagination
        $filteredTotals = [
            'filtered_payments' => (clone $query)->where('voucher_type', 'payment')->sum('debit_amount'),
            'filtered_receipts' => (clone $query)->where('voucher_type', 'receipt')->sum('credit_amount'),
        ];

        $vouchers = $query->latest('voucher_date')->latest('id')->paginate(20);

        // Stats - This month vouchers
        $stats = [
            'total_payments' => JournalVoucher::where('admin_or_user_id', Auth::id())
                ->where('voucher_type', 'payment')
                ->whereMonth('voucher_date', now()->month)
                ->whereYear('voucher_date', now()->year)
                ->sum('debit_amount'),
            'total_receipts' => JournalVoucher::where('admin_or_user_id', Auth::id())
                ->where('voucher_type', 'receipt')
                ->whereMonth('voucher_date', now()->month)
                ->whereYear('voucher_date', now()->year)
                ->sum('credit_amount'),
            'today_payments' => JournalVoucher::where('admin_or_user_id', Auth::id())
                ->where('voucher_type', 'payment')
                ->whereDate('voucher_date', today())
                ->sum('debit_amount'),
            'today_receipts' => JournalVoucher::where('admin_or_user_id', Auth::id())
                ->where('voucher_type', 'receipt')
                ->whereDate('voucher_date', today())
                ->sum('credit_amount'),
        ];

        // Get lists for dropdowns
        $vendors = Vendor::where('admin_or_user_id', Auth::id())->get();
        $customers = Customer::where('admin_or_user_id', Auth::id())->get();
        $contractors = Contractor::where('admin_or_user_id', Auth::id())->get();
        $staffs = Salesman::where('admin_or_user_id', Auth::id())->where('status', 1)->get();
        $expenseHeads = Expense::where('admin_or_user_id', Auth::id())->get();

        return view('admin_panel.journal_voucher.index', compact(
            'vouchers', 'stats', 'filteredTotals', 'vendors', 'customers', 'contractors', 'staffs', 'expenseHeads'
        ));
    }

    // Get party list by type (AJAX)
    public function getParties($type)
    {
        $parties = [];

        switch ($type) {
            case 'vendor':
                // Get vendors with balance from vendor_ledgers
                $parties = Vendor::where('admin_or_user_id', Auth::id())
                    ->with('ledger')
                    ->get()
                    ->map(function($v) {
                        return [
                            'id' => $v->id,
                            'name' => $v->Party_name,
                            'balance' => $v->ledger ? $v->ledger->closing_balance : 0
                        ];
                    });
                break;
            case 'customer':
                // Get customers with balance from customer_ledgers
                $parties = Customer::where('admin_or_user_id', Auth::id())
                    ->get()
                    ->map(function($c) {
                        $ledger = CustomerLedger::where('customer_id', $c->id)->first();
                        return [
                            'id' => $c->id,
                            'name' => $c->customer_name,
                            'balance' => $ledger ? $ledger->closing_balance : 0
                        ];
                    });
                break;
            case 'contractor':
                // Get contractors with balance from contractor_ledgers
                $parties = Contractor::where('admin_or_user_id', Auth::id())
                    ->with('ledger')
                    ->get()
                    ->map(function($c) {
                        return [
                            'id' => $c->id,
                            'name' => $c->contractor_name,
                            'balance' => $c->ledger ? $c->ledger->closing_balance : 0
                        ];
                    });
                break;
            case 'staff':
                $parties = Salesman::where('admin_or_user_id', Auth::id())
                    ->where('status', 1)
                    ->get()
                    ->map(function($s) {
                        return [
                            'id' => $s->id,
                            'name' => $s->name,
                            'balance' => $s->salary ?? 0
                        ];
                    });
                break;
            case 'expense':
                $parties = Expense::where('admin_or_user_id', Auth::id())
                    ->get()
                    ->map(function($e) {
                        return [
                            'id' => $e->id,
                            'name' => $e->expense_name,
                            'balance' => 0
                        ];
                    });
                break;
        }

        return response()->json(['success' => true, 'parties' => $parties]);
    }

    // Store Payment Voucher
    public function storePayment(Request $request)
    {
        $request->validate([
            'party_type' => 'required',
            'party_id' => 'required',
            'amount' => 'required|numeric|min:0.01',
            'voucher_date' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $voucherNo = JournalVoucher::generateVoucherNo('payment');
            $partyName = $this->getPartyName($request->party_type, $request->party_id);

            // Create voucher
            $voucher = JournalVoucher::create([
                'admin_or_user_id' => Auth::id(),
                'voucher_no' => $voucherNo,
                'voucher_date' => $request->voucher_date,
                'voucher_type' => 'payment',
                'party_type' => $request->party_type,
                'party_id' => $request->party_id,
                'party_name' => $partyName,
                'account_head' => $request->account_head ?? 'Payment',
                'debit_amount' => $request->amount,
                'credit_amount' => 0,
                'payment_method' => $request->payment_method ?? 'cash',
                'bank_name' => $request->bank_name,
                'cheque_no' => $request->cheque_no,
                'cheque_date' => $request->cheque_date,
                'narration' => $request->narration,
                'remarks' => $request->remarks,
                'status' => 'approved',
            ]);

            // Update respective ledgers
            $this->updateLedgerForPayment($request);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment Voucher ' . $voucherNo . ' created successfully!',
                'voucher' => $voucher
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payment Voucher Error: ' . $e->getMessage() . ' at line ' . $e->getLine() . ' in ' . $e->getFile());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'debug' => [
                    'line' => $e->getLine(),
                    'file' => basename($e->getFile())
                ]
            ], 500);
        }

    }

    // Store Receipt Voucher
    public function storeReceipt(Request $request)
    {
        $request->validate([
            'party_type' => 'required',
            'party_id' => 'required',
            'amount' => 'required|numeric|min:0.01',
            'voucher_date' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $voucherNo = JournalVoucher::generateVoucherNo('receipt');
            $partyName = $this->getPartyName($request->party_type, $request->party_id);

            // Create voucher
            $voucher = JournalVoucher::create([
                'admin_or_user_id' => Auth::id(),
                'voucher_no' => $voucherNo,
                'voucher_date' => $request->voucher_date,
                'voucher_type' => 'receipt',
                'party_type' => $request->party_type,
                'party_id' => $request->party_id,
                'party_name' => $partyName,
                'account_head' => $request->account_head ?? 'Receipt',
                'debit_amount' => 0,
                'credit_amount' => $request->amount,
                'payment_method' => $request->payment_method ?? 'cash',
                'bank_name' => $request->bank_name,
                'cheque_no' => $request->cheque_no,
                'cheque_date' => $request->cheque_date,
                'narration' => $request->narration,
                'remarks' => $request->remarks,
                'status' => 'approved',
            ]);

            // Update respective ledgers
            $this->updateLedgerForReceipt($request);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Receipt Voucher ' . $voucherNo . ' created successfully!',
                'voucher' => $voucher
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Get single voucher
    public function show($id)
    {
        $voucher = JournalVoucher::where('admin_or_user_id', Auth::id())->findOrFail($id);
        return response()->json(['success' => true, 'data' => $voucher]);
    }

    // Update voucher
    public function update(Request $request, $id)
    {
        $voucher = JournalVoucher::where('admin_or_user_id', Auth::id())->findOrFail($id);

        $voucher->update([
            'voucher_date' => $request->voucher_date ?? $voucher->voucher_date,
            'payment_method' => $request->payment_method ?? $voucher->payment_method,
            'bank_name' => $request->bank_name,
            'cheque_no' => $request->cheque_no,
            'cheque_date' => $request->cheque_date,
            'narration' => $request->narration,
            'remarks' => $request->remarks,
        ]);

        return response()->json(['success' => true, 'message' => 'Voucher updated successfully!']);
    }

    // Delete voucher
    public function destroy($id)
    {
        $voucher = JournalVoucher::where('admin_or_user_id', Auth::id())->findOrFail($id);

        // Reverse ledger entries if needed
        $this->reverseLedgerEntry($voucher);

        $voucher->delete();

        return response()->json(['success' => true, 'message' => 'Voucher deleted successfully!']);
    }

    // Print Voucher
    public function print($id)
    {
        $voucher = JournalVoucher::where('admin_or_user_id', Auth::id())->findOrFail($id);
        return view('admin_panel.journal_voucher.print', compact('voucher'));
    }

    // Helper: Get party name - Using correct field names
    private function getPartyName($type, $id)
    {
        if (!$id) return null;

        return match($type) {
            'vendor' => Vendor::find($id)?->Party_name,
            'customer' => Customer::find($id)?->customer_name,
            'contractor' => Contractor::find($id)?->contractor_name,
            'staff' => Salesman::find($id)?->name,
            'expense' => Expense::find($id)?->expense_name,
            default => null,
        };
    }

    // Helper: Update ledger for payment
    private function updateLedgerForPayment($request)
    {
        $amount = $request->amount;
        $partyId = $request->party_id;

        try {
            switch ($request->party_type) {
                case 'vendor':
                    // Payment to vendor - decrease vendor ledger closing balance
                    $ledger = VendorLedger::firstOrCreate(
                        ['vendor_id' => $partyId],
                        ['admin_or_user_id' => Auth::id(), 'opening_balance' => 0, 'previous_balance' => 0, 'closing_balance' => 0]
                    );
                    $ledger->previous_balance = $ledger->closing_balance;
                    $ledger->closing_balance -= $amount;
                    $ledger->save();
                    break;

                case 'contractor':
                    // Payment to contractor - decrease contractor ledger closing balance
                    $ledger = ContractorLedger::firstOrCreate(
                        ['contractor_id' => $partyId],
                        ['admin_or_user_id' => Auth::id(), 'opening_balance' => 0, 'previous_balance' => 0, 'closing_balance' => 0]
                    );
                    $ledger->previous_balance = $ledger->closing_balance;
                    $ledger->closing_balance -= $amount;
                    $ledger->save();
                    break;

                case 'staff':
                    // Staff salary or advance
                    StaffAdvance::create([
                        'admin_or_user_id' => Auth::id(),
                        'staff_id' => $partyId,
                        'amount' => $amount,
                        'remaining_amount' => $amount,
                        'date' => $request->voucher_date,
                        'remarks' => $request->narration ?? 'Payment via Journal Voucher',
                    ]);
                    break;


                case 'expense':
                    // Record expense - using correct add_expenses table columns
                    AddExpense::create([
                        'admin_or_user_id' => Auth::id(),
                        'expense_id' => $partyId,
                        'amount' => $amount,
                        'expense_date' => $request->voucher_date,
                        'description' => $request->narration ?? 'Via Journal Voucher',
                    ]);
                    break;
            }
        } catch (\Exception $e) {
            \Log::error('Ledger Update Error: ' . $e->getMessage());
            throw $e; // Re-throw to maintain transaction integrity
        }
    }


    // Helper: Update ledger for receipt
    private function updateLedgerForReceipt($request)
    {
        $amount = $request->amount;
        $partyId = $request->party_id;

        try {
            switch ($request->party_type) {
                case 'customer':
                    // Receipt from customer - decrease their receivable balance
                    $ledger = CustomerLedger::firstOrCreate(
                        ['customer_id' => $partyId],
                        ['admin_or_user_id' => Auth::id(), 'opening_balance' => 0, 'previous_balance' => 0, 'closing_balance' => 0]
                    );
                    $ledger->previous_balance = $ledger->closing_balance;
                    $ledger->closing_balance -= $amount;
                    $ledger->save();
                    break;

                case 'vendor':
                    // Refund/Receipt from vendor - decreases our receivable / increases our payable back
                    $ledger = VendorLedger::firstOrCreate(
                        ['vendor_id' => $partyId],
                        ['admin_or_user_id' => Auth::id(), 'opening_balance' => 0, 'previous_balance' => 0, 'closing_balance' => 0]
                    );
                    $ledger->previous_balance = $ledger->closing_balance;
                    $ledger->closing_balance += $amount;
                    $ledger->save();
                    break;

                case 'contractor':
                    // Receipt from contractor
                    $ledger = ContractorLedger::firstOrCreate(
                        ['contractor_id' => $partyId],
                        ['admin_or_user_id' => Auth::id(), 'opening_balance' => 0, 'previous_balance' => 0, 'closing_balance' => 0]
                    );
                    $ledger->previous_balance = $ledger->closing_balance;
                    $ledger->closing_balance += $amount;
                    $ledger->save();
                    break;
            }
        } catch (\Exception $e) {
            \Log::error('Receipt Ledger Error: ' . $e->getMessage());
            throw $e;
        }
    }


    // Helper: Reverse ledger entry on delete
    private function reverseLedgerEntry($voucher)
    {
        $amount = $voucher->voucher_type === 'payment' ? $voucher->debit_amount : $voucher->credit_amount;
        $partyId = $voucher->party_id;

        if (!$partyId) return;

        if ($voucher->voucher_type === 'payment') {
            // Reverse payment - add back to ledger
            switch ($voucher->party_type) {
                case 'vendor':
                    $ledger = VendorLedger::where('vendor_id', $partyId)->first();
                    if ($ledger) {
                        $ledger->closing_balance += $amount;
                        $ledger->save();
                    }
                    break;
                case 'contractor':
                    $ledger = ContractorLedger::where('contractor_id', $partyId)->first();
                    if ($ledger) {
                        $ledger->closing_balance += $amount;
                        $ledger->save();
                    }
                    break;
            }
        } else {
            // Reverse receipt - add back to ledger
            switch ($voucher->party_type) {
                case 'customer':
                    $ledger = CustomerLedger::where('customer_id', $partyId)->first();
                    if ($ledger) {
                        $ledger->closing_balance += $amount;
                        $ledger->save();
                    }
                    break;
                case 'vendor':
                    // Reverse receipt - remove from ledger
                    $ledger = VendorLedger::where('vendor_id', $partyId)->first();
                    if ($ledger) {
                        $ledger->closing_balance -= $amount;
                        $ledger->save();
                    }
                    break;
            }
        }
    }

    // Day Book Report
    public function dayBook(Request $request)
    {
        $date = $request->date ?? today()->format('Y-m-d');

        $vouchers = JournalVoucher::where('admin_or_user_id', Auth::id())
            ->whereDate('voucher_date', $date)
            ->orderBy('id')
            ->get();

        $totalPayments = $vouchers->where('voucher_type', 'payment')->sum('debit_amount');
        $totalReceipts = $vouchers->where('voucher_type', 'receipt')->sum('credit_amount');

        return view('admin_panel.journal_voucher.day_book', compact('vouchers', 'date', 'totalPayments', 'totalReceipts'));
    }

    // ✅ Daily Closing Dashboard
    public function dailyClosing(Request $request)
    {
        $date = $request->date ?? today()->format('Y-m-d');
        $userId = Auth::id();

        // Get all vouchers for the day
        $vouchers = JournalVoucher::where('admin_or_user_id', $userId)
            ->whereDate('voucher_date', $date)
            ->orderBy('created_at')
            ->get();

        // Calculate financial totals
        $totalReceipts = $vouchers->where('voucher_type', 'receipt')->sum('credit_amount');
        $totalPayments = $vouchers->where('voucher_type', 'payment')->sum('debit_amount');
        $netCashFlow = $totalReceipts - $totalPayments;

        // Get business metrics for today
        $businessMetrics = $this->getDailyBusinessMetrics($date, $userId);

        // Party-wise summary with breakdown
        $partySummary = $vouchers->groupBy('party_type')->map(function($partyVouchers, $partyType) {
            return [
                'party_type' => $partyType,
                'total_receipts' => $partyVouchers->where('voucher_type', 'receipt')->sum('credit_amount'),
                'total_payments' => $partyVouchers->where('voucher_type', 'payment')->sum('debit_amount'),
                'count' => $partyVouchers->count(),
                'vouchers' => $partyVouchers
            ];
        });

        // Opening and closing cash balance calculation
        $previousClosingBalance = $this->getPreviousDayClosingBalance($date);
        $currentClosingBalance = $previousClosingBalance + $netCashFlow;

        // Check if day is already closed
        $isDayClosed = $this->isDayClosed($date);

        return view('admin_panel.journal_voucher.daily_closing', compact(
            'vouchers', 'date', 'totalReceipts', 'totalPayments', 'netCashFlow',
            'partySummary', 'previousClosingBalance', 'currentClosingBalance', 'isDayClosed', 'businessMetrics'
        ));
    }

    // ✅ Close Day
    public function closeDayFinally(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'cash_in_hand' => 'required|numeric',
            'remarks' => 'nullable|string'
        ]);

        $date = $request->date;
        $userId = Auth::id();

        // Check if already closed
        if ($this->isDayClosed($date)) {
            return response()->json([
                'success' => false,
                'message' => 'This day is already closed!'
            ]);
        }

        // Get day's vouchers
        $vouchers = JournalVoucher::where('admin_or_user_id', $userId)
            ->whereDate('voucher_date', $date)
            ->get();

        $totalReceipts = $vouchers->where('voucher_type', 'receipt')->sum('credit_amount');
        $totalPayments = $vouchers->where('voucher_type', 'payment')->sum('debit_amount');
        $calculatedClosing = $this->getPreviousDayClosingBalance($date) + ($totalReceipts - $totalPayments);

        // Get business metrics for the day
        $businessMetrics = $this->getDailyBusinessMetrics($date, $userId);

        // Create daily closing record
        DB::table('daily_closings')->insert([
            'admin_or_user_id' => $userId,
            'closing_date' => $date,
            'opening_balance' => $this->getPreviousDayClosingBalance($date),
            'total_receipts' => $totalReceipts,
            'total_payments' => $totalPayments,
            'calculated_closing' => $calculatedClosing,
            'actual_cash_in_hand' => $request->cash_in_hand,
            'variance' => $request->cash_in_hand - $calculatedClosing,
            'vouchers_count' => $vouchers->count(),
            'remarks' => $request->remarks,
            // Business metrics
            'total_jobs' => $businessMetrics['total_jobs'],
            'total_job_amount' => $businessMetrics['total_job_amount'],
            'assigned_jobs' => $businessMetrics['assigned_jobs'],
            'contractor_payments' => $businessMetrics['contractor_payments'],
            'vendor_payments' => $businessMetrics['vendor_payments'],
            'expense_payments' => $businessMetrics['expense_payments'],
            'customer_recoveries' => $businessMetrics['customer_recoveries'],
            'staff_payments' => $businessMetrics['staff_payments'],
            'closed_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Day closed successfully!',
            'closing_balance' => $request->cash_in_hand,
            'variance' => $request->cash_in_hand - $calculatedClosing
        ]);
    }

    // ✅ Get Previous Day's Closing Balance
    private function getPreviousDayClosingBalance($date)
    {
        $previousClosing = DB::table('daily_closings')
            ->where('admin_or_user_id', Auth::id())
            ->where('closing_date', '<', $date)
            ->orderBy('closing_date', 'desc')
            ->first();

        return $previousClosing ? $previousClosing->actual_cash_in_hand : 0;
    }

    // ✅ Check if Day is Already Closed
    private function isDayClosed($date)
    {
        return DB::table('daily_closings')
            ->where('admin_or_user_id', Auth::id())
            ->where('closing_date', $date)
            ->exists();
    }

    // ✅ Daily Closing History
    public function closingHistory(Request $request)
    {
        $query = DB::table('daily_closings')
            ->where('admin_or_user_id', Auth::id())
            ->orderBy('closing_date', 'desc');

        if ($request->from_date && $request->to_date) {
            $query->whereBetween('closing_date', [$request->from_date, $request->to_date]);
        }

        $closings = $query->paginate(20);

        return view('admin_panel.journal_voucher.closing_history', compact('closings'));
    }

    // ✅ Reopen Day (if needed)
    public function reopenDay(Request $request)
    {
        $date = $request->date;
        $userId = Auth::id();

        // Check if there are any closings after this date
        $futureClosings = DB::table('daily_closings')
            ->where('admin_or_user_id', $userId)
            ->where('closing_date', '>', $date)
            ->count();

        if ($futureClosings > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot reopen this day as future days are already closed!'
            ]);
        }

        DB::table('daily_closings')
            ->where('admin_or_user_id', $userId)
            ->where('closing_date', $date)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Day reopened successfully!'
        ]);
    }

    // ✅ Get Daily Business Metrics
    private function getDailyBusinessMetrics($date, $userId)
    {
        // 1. Local Sales (Total Jobs and Amount) - آپ کے jobs local_sales میں ہیں
        $totalJobs = LocalSale::where('admin_or_user_id', $userId)
            ->whereDate('created_at', $date)
            ->count();

        $totalJobAmount = LocalSale::where('admin_or_user_id', $userId)
            ->whereDate('created_at', $date)
            ->sum('net_amount'); // net_amount field use کر رہے ہیں

        // 2. Job Status Analysis from local_sales
        $completedJobs = LocalSale::where('admin_or_user_id', $userId)
            ->whereDate('created_at', $date)
            ->where('job_status', 'completed')
            ->count();

        $pendingJobs = LocalSale::where('admin_or_user_id', $userId)
            ->whereDate('created_at', $date)
            ->where('job_status', 'pending')
            ->count();

        // Also check JobOrder table for any additional jobs
        $additionalJobs = JobOrder::where('admin_or_user_id', $userId)
            ->whereDate('created_at', $date)
            ->count();

        $additionalJobAmount = JobOrder::where('admin_or_user_id', $userId)
            ->whereDate('created_at', $date)
            ->sum('total_amount');

        // 3. Contractor Payments
        $contractorPayments = JournalVoucher::where('admin_or_user_id', $userId)
            ->whereDate('voucher_date', $date)
            ->where('party_type', 'contractor')
            ->where('voucher_type', 'payment')
            ->sum('debit_amount');

        // 4. Vendor Payments
        $vendorPayments = JournalVoucher::where('admin_or_user_id', $userId)
            ->whereDate('voucher_date', $date)
            ->where('party_type', 'vendor')
            ->where('voucher_type', 'payment')
            ->sum('debit_amount');

        // 5. Expense Payments
        $expensePayments = JournalVoucher::where('admin_or_user_id', $userId)
            ->whereDate('voucher_date', $date)
            ->where('party_type', 'expense')
            ->where('voucher_type', 'payment')
            ->sum('debit_amount');

        // 6. Customer Recoveries (Receipts)
        $customerRecoveries = JournalVoucher::where('admin_or_user_id', $userId)
            ->whereDate('voucher_date', $date)
            ->where('party_type', 'customer')
            ->where('voucher_type', 'receipt')
            ->sum('credit_amount');

        // 7. Staff Payments
        $staffPayments = JournalVoucher::where('admin_or_user_id', $userId)
            ->whereDate('voucher_date', $date)
            ->where('party_type', 'staff')
            ->where('voucher_type', 'payment')
            ->sum('debit_amount');

        return [
            'total_jobs' => $totalJobs + $additionalJobs, // Local sales + Job orders
            'total_job_amount' => $totalJobAmount + $additionalJobAmount,
            'assigned_jobs' => $completedJobs, // مکمل jobs کو assigned میں show کریں گے
            'partially_assigned' => $pendingJobs, // Pending jobs کو partial میں
            'contractor_payments' => $contractorPayments,
            'vendor_payments' => $vendorPayments,
            'expense_payments' => $expensePayments,
            'customer_recoveries' => $customerRecoveries,
            'staff_payments' => $staffPayments,
        ];
    }
}
