<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Models\Contractor;
use App\Models\Customer;
use App\Models\CustomerRecovery;
use App\Models\Distributor;
use App\Models\JobOrder;
use App\Models\LocalSale;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Recovery;
use App\Models\Sale;
use App\Models\Salesman;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function Distributor_Ledger_Record()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Distributors = Distributor::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure

            return view('admin_panel.reports.distributor_ledger_record', [
                'Distributors' => $Distributors,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function fetchDistributorLedger(Request $request)
    {
        $distributorId = $request->input('distributor_id');

        $startDate = $request->input('start_date').' 00:00:00';
        $endDate = $request->input('end_date').' 23:59:59';

        $ledger = DB::table('distributor_ledgers')
            ->where('distributor_id', $distributorId)
            ->select('opening_balance')
            ->first();

        $baseOpening = $ledger->opening_balance ?? 0;

        // ---- Transactions Before Start Date ----
        $previousSales = DB::table('sales')
            ->where('distributor_id', $distributorId)
            ->where('Date', '<', $startDate)
            ->sum('net_amount');

        $previousRecoveries = DB::table('recoveries')
            ->where('distributor_ledger_id', $distributorId)
            ->where('date', '<', $startDate)
            ->sum('amount_paid');

        $previousReturns = DB::table('sale_returns')
            ->where('sale_type', 'distributor')
            ->where('party_id', $distributorId)
            ->where('created_at', '<', $startDate)
            ->sum('total_return_amount');

        // ✅ Opening Balance = BaseOpening + (Sales − Recoveries − Returns)
        $openingBalance = $baseOpening + $previousSales - ($previousRecoveries + $previousReturns);

        // ---- Current Period Transactions ----
        $recoveries = DB::table('recoveries')
            ->where('distributor_ledger_id', $distributorId)
            ->whereBetween('date', [$startDate, $endDate])
            ->select('id', 'amount_paid', 'salesman', 'date', 'remarks')
            ->get();

        $sales = DB::table('sales')
            ->where('distributor_id', $distributorId)
            ->whereBetween('Date', [$startDate, $endDate])
            ->select('invoice_number', 'Date', 'Booker', 'Saleman', 'grand_total', 'discount_value', 'scheme_value', 'net_amount')
            ->get();

        $saleReturns = DB::table('sale_returns')
            ->where('sale_type', 'distributor')
            ->where('party_id', $distributorId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('invoice_number', 'created_at', 'total_return_amount')
            ->get();

        // ✅ Closing Balance = Opening + Sales − (Recoveries + Returns)
        $closingBalance = $openingBalance
            + $sales->sum('net_amount')
            - ($recoveries->sum('amount_paid') + $saleReturns->sum('total_return_amount'));

        // ---- Distributor Balance Transfers (Jo is distributor ko mile hain)
        $transfers = DB::table('distributor_balance_transfers')
            ->where('to_distributor', $distributorId)
            ->whereBetween('transfer_date', [$startDate, $endDate])
            ->select('id', 'from_distributor', 'to_distributor', 'amount', 'transfer_date', 'reason')
            ->get();

        return response()->json([
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'recoveries' => $recoveries,
            'sales' => $sales,
            'sale_returns' => $saleReturns,
            'transfers' => $transfers,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function vendor_Ledger_Record()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Vendors = Vendor::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure

            return view('admin_panel.reports.vendor_ledger_record', [
                'Vendors' => $Vendors,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function fetchVendorLedger(Request $request)
    {
        try {
            $vendorId = $request->input('Vendor_id');
            $startDate = $request->input('start_date').' 00:00:00';
            $endDate = $request->input('end_date').' 23:59:59';

            // ---- Get Base Opening from Vendor Ledger ----
            $ledger = DB::table('vendor_ledgers')
                ->where('vendor_id', $vendorId)
                ->select('opening_balance')
                ->first();

            $baseOpening = $ledger ? ($ledger->opening_balance ?? 0) : 0;

            // ---- Transactions Before Start Date ----
            $previousPurchases = DB::table('purchases')
                ->where('party_name', $vendorId)
                ->where('purchase_date', '<', $startDate)
                ->sum('grand_total');

            $previousVendorPayments = DB::table('vendor_payments')
                ->where('vendor_id', $vendorId)
                ->where('payment_date', '<', $startDate)
                ->sum('amount');

            $previousJournalPayments = DB::table('journal_vouchers')
                ->where('party_type', 'vendor')
                ->where('party_id', $vendorId)
                ->where('voucher_type', 'payment')
                ->where('voucher_date', '<', $startDate)
                ->sum('debit_amount');

            $previousPayments = $previousVendorPayments + $previousJournalPayments;

            $previousJournalReceipts = DB::table('journal_vouchers')
                ->where('party_type', 'vendor')
                ->where('party_id', $vendorId)
                ->where('voucher_type', 'receipt')
                ->where('voucher_date', '<', $startDate)
                ->sum('credit_amount');

            $previousReturnsRaw = DB::table('purchase_returns')
                ->where('party_name', $vendorId)
                ->where('return_date', '<', $startDate)
                ->get();

            $previousReturns = 0;
            foreach ($previousReturnsRaw as $return) {
                $amountArray = json_decode($return->return_amount, true);
                $previousReturns += collect($amountArray)->sum();
            }

            // Vendor builties feature incomplete - table has no amount column
            // $previousBuilties = DB::table('vendor_builties')
            //     ->where('vendor_id', $vendorId)
            //     ->where('date', '<', $startDate)
            //     ->sum('amount');
            $previousBuilties = 0;

            $previousSales = DB::table('local_sales')
                ->where('vendor_id', $vendorId)
                ->where('created_at', '<', $startDate)
                ->sum('net_amount');

            $previousSalesAdvances = DB::table('local_sales')
                ->where('vendor_id', $vendorId)
                ->where('created_at', '<', $startDate)
                ->sum('advance_amount');

            // Job Orders before Start Date (vendor assigned jobs - Debit = total_amount, Credit = paid_amount)
            $previousJobOrders = DB::table('job_orders')
                ->where('vendor_id', $vendorId)
                ->where('assignee_type', 'vendor')
                ->whereNotNull('vendor_id')
                ->whereNull('deleted_at')
                ->where('order_date', '<', $request->input('start_date'))
                ->sum('total_amount');

            $previousJobPaidAmounts = DB::table('job_orders')
                ->where('vendor_id', $vendorId)
                ->where('assignee_type', 'vendor')
                ->whereNotNull('vendor_id')
                ->whereNull('deleted_at')
                ->where('order_date', '<', $request->input('start_date'))
                ->sum('paid_amount');

            // ✅ Opening Balance = BaseOpening + Purchases + Builties + JobOrders + VendorReceipts + SalesAdvances − (Payments + Returns + LocalSales + JobPaidAmounts)
            $openingBalance = $baseOpening
                + $previousPurchases
                + $previousBuilties
                + $previousJobOrders
                + $previousJournalReceipts
                + $previousSalesAdvances
                - ($previousPayments + $previousReturns + $previousSales + $previousJobPaidAmounts);

            // ---- Current Period Transactions ----
            $recoveries = DB::table('vendor_payments')
                ->where('vendor_id', $vendorId)
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->select('id', 'amount', 'remarks', 'payment_date')
                ->get();

            $journalRecoveries = DB::table('journal_vouchers')
                ->where('party_type', 'vendor')
                ->where('party_id', $vendorId)
                ->where('voucher_type', 'payment')
                ->whereBetween('voucher_date', [$startDate, $endDate])
                ->select('id', 'debit_amount as amount', 'narration as remarks', 'voucher_date as payment_date')
                ->get();

            $recoveries = $recoveries->concat($journalRecoveries);

            $journalReceipts = DB::table('journal_vouchers')
                ->where('party_type', 'vendor')
                ->where('party_id', $vendorId)
                ->where('voucher_type', 'receipt')
                ->whereBetween('voucher_date', [$startDate, $endDate])
                ->select('id', 'credit_amount as amount', 'narration as remarks', 'voucher_date as receipt_date')
                ->get();

            $purchases = DB::table('purchases')
                ->where('party_name', $vendorId)
                ->whereBetween('purchase_date', [$startDate, $endDate])
                ->select('id', 'invoice_number', 'purchase_date', 'grand_total', 'item')
                ->get()
                ->map(function ($purchase) {
                    return [
                        'invoice_number' => $purchase->invoice_number,
                        'date' => $purchase->purchase_date,
                        'grand_total' => $purchase->grand_total,
                        'net_amount' => $purchase->grand_total,
                        'items' => $purchase->item,
                    ];
                });

            $returnsRaw = DB::table('purchase_returns')
                ->where('party_name', $vendorId)
                ->whereBetween('return_date', [$startDate, $endDate])
                ->get();

            $returns = [];
            $currentReturns = 0;
            foreach ($returnsRaw as $return) {
                $amountArray = json_decode($return->return_amount, true);
                $amountSum = collect($amountArray)->sum();
                $currentReturns += $amountSum;

                $returns[] = [
                    'id' => $return->id,
                    'invoice_number' => $return->invoice_number,
                    'date' => $return->return_date,
                    'net_amount' => $amountSum,
                ];
            }

            // Vendor builties feature incomplete - table has no amount column
            // $builties = DB::table('vendor_builties')
            //     ->where('vendor_id', $vendorId)
            //     ->whereBetween('date', [$startDate, $endDate])
            //     ->select('id', 'date', 'amount', 'description')
            //     ->get();
            $builties = collect([]);

            $local_sales = DB::table('local_sales')
                ->where('vendor_id', $vendorId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select('invoice_number', 'created_at as Date', 'net_amount', 'advance_amount', 'item')
                ->get();

            // Job Orders for current period (vendor assigned jobs)
            $jobOrders = DB::table('job_orders')
                ->where('vendor_id', $vendorId)
                ->where('assignee_type', 'vendor')
                ->whereNotNull('vendor_id')
                ->whereNull('deleted_at')
                ->whereBetween('order_date', [$request->input('start_date'), $request->input('end_date')])
                ->select('id', 'job_order_number', 'order_date', 'total_amount', 'paid_amount', 'remaining_amount', 'description', 'assignment_status')
                ->get();

            $totalJobOrders = $jobOrders->sum('total_amount');
            $totalJobPaid   = $jobOrders->sum('paid_amount');

            // ✅ Closing Balance = Opening + Purchases + JobOrders + VendorReceipts + SalesAdvance − (Payments + Returns + Local Sales + JobPaidAmounts)
            // Note: Builties excluded as table has no amount field
            $closingBalance = $openingBalance
                + $purchases->sum('grand_total')
                + $totalJobOrders
                + $journalReceipts->sum('amount')
                + $local_sales->sum('advance_amount')
                // + $builties->sum('amount')  // Excluded - no amount column
                - ($recoveries->sum('amount') + $currentReturns + $local_sales->sum('net_amount') + $totalJobPaid);

            return response()->json([
                'opening_balance' => $openingBalance,
                'closing_balance' => $closingBalance,
                'purchases' => $purchases,
                'recoveries' => $recoveries,
                'receipts' => $journalReceipts,
                'returns' => $returns,
                'builties' => $builties,
                'local_sales' => $local_sales,
                'job_orders' => $jobOrders,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ]);
        } catch (\Exception $e) {
            \Log::error('Vendor Ledger Error: ' . $e->getMessage());
            \Log::error('Stack Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ], 500);
        }
    }

    public function Customer_Ledger_Record()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Customers = Customer::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure

            return view('admin_panel.reports.customer_ledger_record', [
                'Customers' => $Customers,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function fetchCustomerLedger(Request $request)
    {
        try {
            $CustomerId = $request->input('Customer_id');
            if (! $CustomerId) {
                return response()->json(['message' => 'Customer_id is required'], 422);
            }

            $startDate = ($request->input('start_date') ?: date('Y-m-d')).' 00:00:00';
            $endDate = ($request->input('end_date') ?: now()->format('Y-m-d')).' 23:59:59';

            /* ---- Base opening from ledger (NO carry-forward) ---- */
            $ledgerRow = DB::table('customer_ledgers')
                ->where('customer_id', $CustomerId)
                ->latest('id')
                ->select('id', 'opening_balance', 'closing_balance', 'created_at')
                ->first();

            $openingBalance = $ledgerRow ? (float) ($ledgerRow->opening_balance ?? 0) : 0;
            $carryFwd = $ledgerRow ? (float) ($ledgerRow->closing_balance ?? 0) : 0;

            // ---- Calculate Opening Balance relative to Start Date ----
            // 1. Sales before Start Date
            $previousSales = DB::table('local_sales')
                ->where('customer_id', $CustomerId)
                ->where('created_at', '<', $startDate)
                ->sum('net_amount');

            // 2. Advances before Start Date
            $previousAdvances = DB::table('local_sales')
                ->where('customer_id', $CustomerId)
                ->where('created_at', '<', $startDate)
                ->sum('advance_amount');

            // 3. Recoveries before Start Date
            $previousRecoveries = DB::table('customer_recoveries as cr')
                ->join('customer_ledgers as cl', 'cl.id', '=', 'cr.customer_ledger_id')
                ->where('cl.customer_id', $CustomerId)
                ->where('cr.date', '<', $startDate)
                ->sum('cr.amount_paid');

            // 4. Returns before Start Date
        $previousReturns = DB::table('sale_returns')
            ->where('party_type', 'customer')
            ->where('party_id', $CustomerId)
            ->where('created_at', '<', $startDate)
            ->sum('return_amount');

        // 5. Journal Receipts before Start Date (Customer pays us)
        $previousJournalReceipts = DB::table('journal_vouchers')
            ->where('party_type', 'customer')
            ->where('party_id', $CustomerId)
            ->where('voucher_type', 'receipt')
            ->where('voucher_date', '<', $startDate)
            ->sum('credit_amount');

        // Correct Opening Balance = Base + Sales - Advances - Recoveries - Returns - Journal Receipts
        $openingBalance = $openingBalance + $previousSales - ($previousAdvances + $previousRecoveries + $previousReturns + $previousJournalReceipts);

            /* ---- Period details (for listing) ---- */
            $recoveries = DB::table('customer_recoveries as cr')
                ->join('customer_ledgers as cl', 'cl.id', '=', 'cr.customer_ledger_id')
                ->where('cl.customer_id', $CustomerId)
                ->whereBetween('cr.date', [$startDate, $endDate])
                ->select('cr.id', 'cr.amount_paid', 'cr.salesman', 'cr.date', 'cr.remarks')
                ->get();

            $localSales = DB::table('local_sales')
                ->where('customer_id', $CustomerId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select('invoice_number', 'sale_date', 'customer_shopname', 'grand_total',
                    'discount_value', 'net_amount', 'advance_amount', 'created_at')
                ->get();

            $saleReturns = DB::table('sale_returns')
            ->where('party_type', 'customer')
            ->where('party_id', $CustomerId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('return_amount', 'created_at', 'reason')
            ->get();

        $journalReceipts = DB::table('journal_vouchers')
            ->where('party_type', 'customer')
            ->where('party_id', $CustomerId)
            ->where('voucher_type', 'receipt')
            ->whereBetween('voucher_date', [$startDate, $endDate])
            ->select('id', 'credit_amount', 'narration as remarks', 'voucher_date')
            ->get();

        // ---- Closing Balance Calculation ----
        $currentSales = $localSales->sum('net_amount');
        $currentAdvances = $localSales->sum('advance_amount');
        $currentRecoveries = $recoveries->sum('amount_paid');
        $currentReturns = $saleReturns->sum('return_amount');
        $currentJournalReceipts = $journalReceipts->sum('credit_amount');

        $closingBalance = $openingBalance + $currentSales - ($currentAdvances + $currentRecoveries + $currentReturns + $currentJournalReceipts);
            
            return response()->json([
                'opening_balance' => round($openingBalance, 2),
                'closing_balance' => round($closingBalance, 2),
                'opening_as_of_start' => round($openingBalance, 2),
            'closing_as_of_end' => round($closingBalance, 2),
            'ledger_closing_balance' => round($carryFwd, 2),
            'recoveries' => $recoveries,
            'receipts' => $journalReceipts,
            'local_sales' => $localSales,
            'sale_returns' => $saleReturns,
            'startDate' => $startDate,
                'endDate' => $endDate,
            ]);
        } catch (\Exception $e) {
            \Log::error('Customer Ledger Error: ' . $e->getMessage());
            \Log::error('Stack Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ], 500);
        }
    }

    public function stock_Record()
    {
        if (Auth::id()) {
            return view('admin_panel.reports.stock_Record');
        } else {
            return redirect()->back();
        }
    }

    public function getItems($subcategory)
    {
        $items = Product::where('sub_category', $subcategory)->select('item_code', 'item_name')->get();

        return response()->json($items);
    }



    public function getItemDetails(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $query = Product::query();

        if ($request->search && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('item_name', 'LIKE', "%$search%")
                  ->orWhere('item_code', 'LIKE', "%$search%");
            });
        }

        $items = $query->get();

        foreach ($items as $item) {
            // 1. Purchase Qty (Stock In) - Count total pcs purchased in date range
            $totalStockIn = 0;
            $totalPurchaseValue = 0; // Track total value for weighted average

            $purchaseQuery = DB::table('purchases')
                ->whereJsonContains('item', $item->item_name)
                ->whereNull('deleted_at');

            if ($start_date) {
                $purchaseQuery->where('purchase_date', '>=', $start_date);
            }
            if ($end_date) {
                $purchaseQuery->where('purchase_date', '<=', $end_date);
            }

            $purchases = $purchaseQuery->get(['item', 'pcs', 'rate']);

            foreach ($purchases as $p) {
                $names = json_decode($p->item, true) ?? [];
                $pcs = json_decode($p->pcs, true) ?? [];
                $rates = json_decode($p->rate, true) ?? [];

                foreach ($names as $idx => $n) {
                    if (trim($n) === trim($item->item_name)) {
                        $pQty = (float)($pcs[$idx] ?? 0);
                        $pRate = (float)($rates[$idx] ?? 0);
                        $totalStockIn += $pQty;
                        $totalPurchaseValue += ($pQty * $pRate); // Calculate total purchase value
                    }
                }
            }

            // Subtract Purchase Returns from Stock In and Purchase Value
            $returnQuery = DB::table('purchase_returns')
                ->whereJsonContains('item', $item->item_name)
                ->whereNull('deleted_at');

            if ($start_date) {
                $returnQuery->where('return_date', '>=', $start_date);
            }
            if ($end_date) {
                $returnQuery->where('return_date', '<=', $end_date);
            }

            $returns = $returnQuery->get(['item', 'return_qty', 'rate']);

            if ($returns && $returns->count() > 0) {
                foreach ($returns as $r) {
                    $names = json_decode($r->item ?? '[]', true) ?? [];
                    $returnQtys = json_decode($r->return_qty ?? '[]', true) ?? [];
                    $returnRates = json_decode($r->rate ?? '[]', true) ?? [];

                    if (is_array($names)) {
                        foreach ($names as $idx => $n) {
                            if (trim($n) === trim($item->item_name)) {
                                $rQty = (float)($returnQtys[$idx] ?? 0);
                                $rRate = (float)($returnRates[$idx] ?? 0);
                                $totalStockIn -= $rQty; // Subtract returned qty
                                $totalPurchaseValue -= ($rQty * $rRate); // Subtract return value
                            }
                        }
                    }
                }
            }

            $item->total_stock_in = abs($totalStockIn);

            // 2. Sold Qty (Stock Out) - Count from stock_outs table using total_stock field
            $outQuery = DB::table('stock_outs')
                ->where('product_id', $item->id)
                ->whereNull('deleted_at');

            if ($start_date) {
                $outQuery->where('created_at', '>=', $start_date . ' 00:00:00');
            }
            if ($end_date) {
                $outQuery->where('created_at', '<=', $end_date . ' 23:59:59');
            }

            $item->total_stock_out = abs((float)$outQuery->sum('total_stock'));

            // 3. Available Balance = Purchase Qty - Sold Qty
            $item->balance_stock = $item->total_stock_in - $item->total_stock_out;

            // 4. Calculate Weighted Average Purchase Rate
            $avgPurchaseRate = 0;
            if ($totalStockIn > 0) {
                $avgPurchaseRate = $totalPurchaseValue / $totalStockIn;
            } else {
                // If no purchases in date range, use wholesale price as fallback
                $avgPurchaseRate = (float)($item->wholesale_price ?? 0);
            }

            // 5. Stock Value = Available Balance × Average Purchase Rate
            $item->stock_value = round($item->balance_stock * $avgPurchaseRate, 2);
            $item->avg_purchase_rate = round($avgPurchaseRate, 2); // For display/debugging
        }

        return response()->json($items);
    }

   

    public function date_wise_recovery_report()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Customers = Customer::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure

            $Salesmans = Salesman::where('admin_or_user_id', $userId)
                ->where('designation', 'Saleman')
                ->get();

            return view('admin_panel.reports.date_wise_recovery_report', [
                'Customers' => $Customers,
                'Salesmans' => $Salesmans,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function getRecoveryReport(Request $request)
    {
        $salesman = $request->salesman;
        $type = $request->type;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $recoveries = [];

        // Distributor Recoveries
        if ($type == 'all' || $type == 'distributor') {
            $query = Recovery::whereBetween('date', [$startDate, $endDate]);

            if ($salesman !== 'All') {
                $query->where('salesman', $salesman);
            }

            $distributorRecoveries = $query->get();

            foreach ($distributorRecoveries as $recovery) {
                $distributor = Distributor::find($recovery->distributor_ledger_id);
                $recoveries[] = [
                    'date' => $recovery->date,
                    'shop_name' => '-', // Distributors ke liye shop name nahi hota
                    'party_name' => $distributor->Customer ?? 'N/A',
                    'area' => $distributor->Area ?? 'N/A',
                    'remarks' => $recovery->remarks,
                    'amount_paid' => number_format($recovery->amount_paid),
                    'salesman' => $recovery->salesman ?? '-',
                ];
            }
        }

        // Customer Recoveries
        if ($type == 'all' || $type == 'customer') {
            $query = CustomerRecovery::whereBetween('date', [$startDate, $endDate]);

            if ($salesman !== 'All') {
                $query->where('salesman', $salesman);
            }

            $customerRecoveries = $query->get();

            foreach ($customerRecoveries as $recovery) {
                $customer = DB::table('customers')
                    ->join('customer_ledgers', 'customer_ledgers.customer_id', '=', 'customers.id')
                    ->where('customer_ledgers.id', $recovery->customer_ledger_id)
                    ->select('customers.shop_name', 'customers.customer_name', 'customers.area')
                    ->first();
                $recoveries[] = [
                    'date' => $recovery->date,
                    'shop_name' => $customer->shop_name ?? 'N/A',
                    'party_name' => $customer->customer_name ?? 'N/A',
                    'area' => $customer->area ?? 'N/A',
                    'remarks' => $recovery->remarks,
                    'amount_paid' => number_format($recovery->amount_paid),
                    'salesman' => $recovery->salesman ?? '-',
                ];

            }
        }

        return response()->json($recoveries);
    }

    public function date_wise_purcahse_report()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Customers = Customer::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure

            return view('admin_panel.reports.date_wise_purcahse_report', [
                'Customers' => $Customers,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function fetch_purchase_report(Request $request)
    {
        $userId = Auth::id();
        $start = $request->start_date;
        $end = $request->end_date;

        $purchases = Purchase::where('admin_or_user_id', $userId)
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $report = [];
        $totals = [
            'carton' => 0,
            'pcs' => 0,
            'liter' => 0,
            'net_amount' => 0,
        ];

        foreach ($purchases as $key => $purchase) {
            $items = $purchase->item ?? [];
            $pcs_carton = $purchase->pcs_carton ?? [];
            $carton_qty = json_decode($purchase->carton_qty ?? '[]');
            $pcs = $purchase->pcs ?? [];
            $liter = json_decode($purchase->liter ?? '[]');
            $amounts = $purchase->amount ?? []; // 👈 Use amount field here

            foreach ($items as $i => $item) {
                $netAmount = floatval($amounts[$i] ?? 0);

                $report[] = [
                    'code' => $key + 1,
                    'date' => \Carbon\Carbon::parse($purchase->purchase_date)->format('d-M-Y'),
                    'item' => $item ?? 'N/A',
                    'carton_packing' => $pcs_carton[$i] ?? 0,
                    'carton_qty' => $carton_qty[$i] ?? 0,
                    'pcs' => $pcs[$i] ?? 0,
                    'liter' => $liter[$i] ?? 0,
                    'net_amount' => $netAmount,
                ];

                $totals['carton'] += floatval($carton_qty[$i] ?? 0);
                $totals['pcs'] += floatval($pcs[$i] ?? 0);
                $totals['liter'] += floatval($liter[$i] ?? 0);
                $totals['net_amount'] += $netAmount;
            }
        }

        return response()->json([
            'report' => $report,
            'totals' => $totals,
        ]);
    }

    public function vendor_wise_purcahse_report()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Vendors = Vendor::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure

            return view('admin_panel.reports.vendor_wise_purcahse_report', [
                'Vendors' => $Vendors,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function fetchVendorPurchaseReport(Request $request)
    {
        $userId = Auth::id();
        $start = $request->start_date;
        $end = $request->end_date;
        $vendorId = $request->vendor_id;

        if (! $vendorId) {
            return response()->json(['error' => 'Vendor is required'], 422);
        }

        $purchases = Purchase::where('admin_or_user_id', $userId)
            ->where('party_name', $vendorId) // ✅ match by vendor ID in party_name
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $report = [];
        $totals = [
            'carton' => 0,
            'pcs' => 0,
            'liter' => 0,
            'net_amount' => 0,
        ];

        foreach ($purchases as $key => $purchase) {
            $items = $purchase->item ?? [];
            $pcs_carton = $purchase->pcs_carton ?? [];
            $carton_qty = json_decode($purchase->carton_qty ?? '[]');
            $pcs = $purchase->pcs ?? [];
            $liter = json_decode($purchase->liter ?? '[]');
            $amounts = $purchase->amount ?? []; // ✅ new line

            foreach ($items as $i => $item) {
                $netAmount = floatval($amounts[$i] ?? 0);

                $report[] = [
                    'inv_no' => $purchase->invoice_number ?? 'N/A',
                    'date' => \Carbon\Carbon::parse($purchase->purchase_date)->format('d-M-Y'),
                    'item' => $item ?? 'N/A',
                    'carton_packing' => $pcs_carton[$i] ?? 0,
                    'carton_qty' => $carton_qty[$i] ?? 0,
                    'pcs' => $pcs[$i] ?? 0,
                    'liter' => $liter[$i] ?? 0,
                    'net_amount' => $netAmount,
                ];

                $totals['carton'] += floatval($carton_qty[$i] ?? 0);
                $totals['pcs'] += floatval($pcs[$i] ?? 0);
                $totals['liter'] += floatval($liter[$i] ?? 0);
                $totals['net_amount'] += $netAmount;
            }
        }

        return response()->json([
            'report' => $report,
            'totals' => $totals,
        ]);
    }

    public function contractor_wise_report()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Contractors = Contractor::where('admin_or_user_id', $userId)->get();

            // Get current month dates for default
            $defaultStartDate = date('Y-m-01');
            $defaultEndDate = date('Y-m-d');

            return view('admin_panel.reports.contractor_wise_report', [
                'Contractors' => $Contractors,
                'defaultStartDate' => $defaultStartDate,
                'defaultEndDate' => $defaultEndDate,
            ]);
        } else {
            return redirect()->back();
        }
    }


    public function fetchContractorReport(Request $request)
    {
        try {
            $contractorId = $request->input('contractor_id');
            if (!$contractorId) {
                return response()->json(['message' => 'Contractor ID is required'], 422);
            }

            $startDate = ($request->input('start_date') ?: date('Y-m-01')).' 00:00:00';
            $endDate = ($request->input('end_date') ?: date('Y-m-d')).' 23:59:59';

            /* ---- Base opening from ledger ---- */
            $ledgerRow = DB::table('contractor_ledgers')
                ->where('contractor_id', $contractorId)
                ->latest('id')
                ->select('id', 'opening_balance', 'previous_balance', 'closing_balance', 'created_at')
                ->first();

            $openingBalance = $ledgerRow ? (float) ($ledgerRow->opening_balance ?? 0) : 0;
            $previousBalance = $ledgerRow ? (float) ($ledgerRow->previous_balance ?? 0) : 0;
            $carryFwd = $ledgerRow ? (float) ($ledgerRow->closing_balance ?? 0) : 0;
            $ledgerId = $ledgerRow ? $ledgerRow->id : null;


            // ---- Calculate Opening Balance relative to Start Date ----
            // 1. Job Orders (Work Done) before Start Date
            $previousJobs = DB::table('job_orders')
                ->where('staff_type', 'contract')
                ->where('staff_id', $contractorId)
                ->whereNull('deleted_at')
                ->where('order_date', '<', $startDate)
                ->sum('total_amount');

            // 2. Paid amounts from jobs before Start Date
            $previousJobPaid = DB::table('job_orders')
                ->where('staff_type', 'contract')
                ->where('staff_id', $contractorId)
                ->whereNull('deleted_at')
                ->where('order_date', '<', $startDate)
                ->sum('paid_amount');

            // 3. Recoveries (Payments to Contractor) before Start Date
            $previousRecoveries = 0;
            if ($ledgerId) {
                $previousRecoveries = DB::table('contractor_recoveries')
                    ->where('contractor_ledger_id', $ledgerId)
                    ->whereNull('deleted_at')
                    ->where('recovery_date', '<', $startDate)
                    ->sum('amount');
            }

            // Opening Balance = Base Opening + Previous Work - Previous Payments
            $openingBalance = $openingBalance + $previousJobs - ($previousJobPaid + $previousRecoveries);

            /* ---- Period details (for listing) ---- */
            // Job Orders in Period
            $jobOrders = DB::table('job_orders')
                ->where('staff_type', 'contract')
                ->where('staff_id', $contractorId)
                ->whereNull('deleted_at')
                ->whereBetween('order_date', [$startDate, $endDate])
                ->select('id', 'job_order_number', 'order_date', 'description', 'total_amount', 'paid_amount', 'remaining_amount', 'status')
                ->orderBy('order_date')
                ->get();

            // Payments Given (from Journal Vouchers) in Period
            $payments = DB::table('journal_vouchers')
                ->where('party_type', 'contractor')
                ->where('party_id', $contractorId)
                ->where('voucher_type', 'payment')
                ->whereNull('deleted_at')
                ->whereBetween('voucher_date', [$startDate, $endDate])
                ->select('id', 'voucher_no', 'debit_amount as amount', 'voucher_date', 'narration', 'payment_method')
                ->orderBy('voucher_date')
                ->get();


            // Also check contractor_recoveries table for legacy payments
            $legacyPayments = collect([]);
            if ($ledgerId) {
                $legacyPayments = DB::table('contractor_recoveries')
                    ->where('contractor_ledger_id', $ledgerId)
                    ->whereNull('deleted_at')
                    ->whereBetween('recovery_date', [$startDate, $endDate])
                    ->select('id', 'amount', 'recovery_date as voucher_date', 'remarks as narration')
                    ->orderBy('recovery_date')
                    ->get();
            }

            // Merge both payment sources
            $allPayments = $payments->merge($legacyPayments);


            // ---- Closing Balance Calculation ----
            $currentJobsTotal = $jobOrders->sum('total_amount');
            $currentJobsPaid = $jobOrders->sum('paid_amount');
            $currentPaymentsGiven = $allPayments->sum('amount');

            $closingBalance = $openingBalance + $currentJobsTotal - ($currentJobsPaid + $currentPaymentsGiven);

            return response()->json([
                'opening_balance' => round($openingBalance, 2),
                'closing_balance' => round($closingBalance, 2),
                'ledger_closing_balance' => round($carryFwd, 2),
                'job_orders' => $jobOrders,
                'payments' => $allPayments,  // Changed from 'recoveries' to 'payments'
                'totals' => [
                    'total_work' => round($currentJobsTotal, 2),
                    'total_paid' => round($currentJobsPaid + $currentPaymentsGiven, 2),
                    'balance' => round($closingBalance, 2),
                ],
                'startDate' => $startDate,
                'endDate' => $endDate,
            ]);

        } catch (\Exception $e) {
            \Log::error('Contractor Ledger Error: ' . $e->getMessage());
            \Log::error('Stack Trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ], 500);
        }
    }


    public function staff_wise_report()
{
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    $userId = Auth::id();

    // Get all active staff with their names
    $staffs = Salesman::where('admin_or_user_id', $userId)
        ->where('status', 1)
        ->whereNotNull('name')
        ->select('id', 'name', 'designation', 'salary', 'phone', 'created_at')
        ->orderBy('name')
        ->get();

    // Get overall summary
    $summary = DB::table('staff_ledgers as sl')
        ->join('sales_mens as sm', 'sl.staff_id', '=', 'sm.id')
        ->where('sl.admin_or_user_id', $userId)
        ->whereNotNull('sl.week_start')
        ->whereNotNull('sm.name')
        ->select(
            DB::raw('COUNT(DISTINCT sl.staff_id) as total_staff'),
            DB::raw('COUNT(sl.id) as total_weeks'),
            DB::raw('COALESCE(SUM(sl.weekly_amount), 0) as total_weekly_amount'),
            DB::raw('COALESCE(SUM(sl.advance), 0) as total_advances'),
            DB::raw('COALESCE(SUM(sl.paid), 0) as total_paid'),
            DB::raw('COALESCE(SUM(sl.balance), 0) as total_balance')
        )
        ->first();

    return view('admin_panel.reports.staff_wise_report', compact('staffs', 'summary'));
}

public function fetchStaffReport(Request $request)
{
    $userId = Auth::id();
    $staffId = $request->staff_id;

    if (! $staffId) {
        return response()->json(['error' => 'Staff is required'], 422);
    }

    // ================= STAFF =================
    $staff = DB::table('sales_mens')
        ->where('id', $staffId)
        ->where('admin_or_user_id', $userId)
        ->first();

    if (! $staff) {
        return response()->json(['error' => 'Staff not found'], 404);
    }

    // ================= LEDGER =================
    $ledger = DB::table('staff_ledgers')
        ->where('saleman_id', $staffId)
        ->where('admin_or_user_id', $userId)
        ->orderBy('ledger_date', 'desc')
        ->get();

    $totals = [
        'opening' => 0,
        'previous' => 0,
        'closing' => 0,
    ];

    if ($ledger->count()) {
        $totals['opening'] = $ledger->first()->opening_balance;
        $totals['previous'] = $ledger->first()->previous_balance;
        $totals['closing'] = $ledger->first()->closing_balance;
    }

    return response()->json([
        'staff' => [
            'name' => $staff->name,
            'phone' => $staff->phone,
            'designation' => $staff->designation,
            'salary' => (float) $staff->salary,
            'city' => $staff->city,
            'address' => $staff->address,
        ],
        'ledger' => $ledger,
        'totals' => $totals,
    ]);
}

public function staffWeeklyHistory(Request $request)
{
    $history = DB::table('staff_ledgers as sl')
        ->join('sales_mens as sm', 'sl.staff_id', '=', 'sm.id')
        ->where('sl.staff_id', $request->staff_id)
        ->where('sl.admin_or_user_id', Auth::id())
        ->whereNotNull('sl.week_start')
        ->whereNotNull('sl.week_end')
        ->whereNotNull('sm.name')
        ->select(
            'sl.*',
            'sm.name as staff_name',
            'sm.designation'
        )
        ->orderBy('sl.week_start', 'desc')
        ->get();

    return response()->json($history);
}

// Save weekly payment entry
public function saveStaffWeekly(Request $request)
{
    $userId = Auth::id();
    $staffId = $request->staff_id;
    $weekStart = $request->week_start;
    $weekEnd = $request->week_end;

    // ✅ Check for duplicate week entry
    $existingEntry = DB::table('staff_ledgers')
        ->where('staff_id', $staffId)
        ->where('admin_or_user_id', $userId)
        ->where('week_start', $weekStart)
        ->where('week_end', $weekEnd)
        ->first();

    if ($existingEntry) {
        return response()->json([
            'success' => false,
            'message' => 'Payment already exists for this week! You cannot add payment twice for the same week.'
        ], 422);
    }

    // Get staff name for validation
    $staff = DB::table('sales_mens')
        ->where('id', $staffId)
        ->where('admin_or_user_id', $userId)
        ->first();

    if (!$staff || !$staff->name) {
        return response()->json([
            'success' => false,
            'message' => 'Staff not found or invalid data!'
        ], 404);
    }

    // Get previous balance
    $previousEntry = DB::table('staff_ledgers')
        ->where('staff_id', $staffId)
        ->where('admin_or_user_id', $userId)
        ->whereNotNull('week_start')
        ->orderBy('week_end', 'desc')
        ->first();

    $previousBalance = $previousEntry ? (float)$previousEntry->balance : 0;

    // Calculate new balance
    $weeklyAmount = (float)$request->weekly_amount;
    $paid = (float)($request->paid ?? 0);
    $advance = (float)($request->advance ?? 0);

    // Previous Balance + Weekly - Advance - Paid
    $newBalance = $previousBalance + $weeklyAmount - $advance - $paid;

    // Save
    DB::table('staff_ledgers')->insert([
        'admin_or_user_id' => $userId,
        'staff_id' => $staffId,
        'week_start' => $weekStart,
        'week_end' => $weekEnd,
        'weekly_amount' => $weeklyAmount,
        'days_present' => $request->days_present ?? 0,
        'days_absent' => $request->days_absent ?? 0,
        'advance' => $advance,
        'paid' => $paid,
        'balance' => $newBalance,
        'note' => $request->note,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Weekly payment saved successfully!'
    ]);
}

// Get comprehensive staff payment summary
public function getStaffPaymentSummary(Request $request)
{
    $userId = Auth::id();
    $staffId = $request->staff_id;

    // Get staff details
    $staff = DB::table('sales_mens')
        ->where('id', $staffId)
        ->where('admin_or_user_id', $userId)
        ->first();

    if (!$staff) {
        return response()->json(['error' => 'Staff not found'], 404);
    }

    // Get all payment records
    $payments = DB::table('staff_ledgers')
        ->where('staff_id', $staffId)
        ->where('admin_or_user_id', $userId)
        ->whereNotNull('week_start')
        ->orderBy('week_start', 'desc')
        ->get();

    // Calculate summary
    $totalWeeks = $payments->count();
    $totalWeeklyAmount = $payments->sum('weekly_amount');
    $totalAdvances = $payments->sum('advance');
    $totalPaid = $payments->sum('paid');
    $currentBalance = $payments->first()->balance ?? 0;

    // Get staff with outstanding balances
    $staffWithBalance = DB::table('staff_ledgers as sl')
        ->join('sales_mens as sm', 'sl.staff_id', '=', 'sm.id')
        ->where('sl.admin_or_user_id', $userId)
        ->whereNotNull('sl.week_start')
        ->select(
            'sm.id',
            'sm.name',
            'sm.designation',
            DB::raw('MAX(sl.week_end) as last_payment_date'),
            DB::raw('(SELECT balance FROM staff_ledgers WHERE staff_id = sm.id AND admin_or_user_id = '.$userId.' AND week_start IS NOT NULL ORDER BY week_end DESC LIMIT 1) as current_balance')
        )
        ->groupBy('sm.id', 'sm.name', 'sm.designation')
        ->having('current_balance', '>', 0)
        ->get();

    return response()->json([
        'staff' => [
            'name' => $staff->name,
            'designation' => $staff->designation,
            'phone' => $staff->phone,
            'salary' => $staff->salary,
        ],
        'summary' => [
            'total_weeks' => $totalWeeks,
            'total_weekly_amount' => $totalWeeklyAmount,
            'total_advances' => $totalAdvances,
            'total_paid' => $totalPaid,
            'current_balance' => $currentBalance,
        ],
        'payments' => $payments,
        'staff_with_balance' => $staffWithBalance
    ]);
}

// Get all staff summary for modal view
public function getAllStaffSummary()
{
    $userId = Auth::id();

    $summary = DB::table('sales_mens as sm')
        ->leftJoin('staff_ledgers as sl', function($join) use ($userId) {
            $join->on('sm.id', '=', 'sl.staff_id')
                ->where('sl.admin_or_user_id', '=', $userId)
                ->whereNotNull('sl.week_start');
        })
        ->where('sm.admin_or_user_id', $userId)
        ->where('sm.status', 1)
        ->whereNotNull('sm.name')
        ->select(
            'sm.id',
            'sm.name as staff_name',
            'sm.designation',
            'sm.salary',
            DB::raw('COUNT(DISTINCT sl.id) as total_weeks'),
            DB::raw('COALESCE(SUM(sl.weekly_amount), 0) as total_weekly'),
            DB::raw('COALESCE(SUM(sl.advance), 0) as total_advance'),
            DB::raw('COALESCE(SUM(sl.paid), 0) as total_paid'),
            DB::raw('(SELECT balance FROM staff_ledgers WHERE staff_id = sm.id AND admin_or_user_id = '.$userId.' AND week_start IS NOT NULL ORDER BY week_end DESC LIMIT 1) as current_balance')
        )
        ->groupBy('sm.id', 'sm.name', 'sm.designation', 'sm.salary')
        ->orderBy('sm.name')
        ->get();

    return response()->json($summary);
}

// Get attendance data for selected week
public function getStaffWeeklyAttendance(Request $request)
{
    $staffId = $request->staff_id;
    $weekStart = $request->week_start;
    $weekEnd = $request->week_end;

    // Count attendance for the week
    $attendance = DB::table('staff_attendences')
        ->where('staff_id', $staffId)
        ->whereBetween('attendence_date', [$weekStart, $weekEnd])
        ->get();

    $present = $attendance->where('status', 'present')->count();
    $absent = $attendance->where('status', 'absent')->count();

    // Get advances for this week
    $advances = DB::table('staff_recoveries')
        ->where('saleman_id', $staffId)
        ->whereBetween('date', [$weekStart, $weekEnd])
        ->where('adjust_type', 'plus')
        ->sum('adjust_amount');

    return response()->json([
        'present' => $present,
        'absent' => $absent,
        'advance' => $advances ?? 0
    ]);
}

    public function Area_wise_Customer_payments()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Customers = Customer::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure
            $cities = City::all(); // Updated the variable name to avoid confusion

            $Salesmans = Salesman::where('admin_or_user_id', $userId)
                ->where('designation', 'Saleman')
                ->get();

            return view('admin_panel.reports.Area_wise_Customer_payments', [
                'Customers' => $Customers,
                'cities' => $cities,
                'Salesmans' => $Salesmans,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function staffWeeklySave(Request $request)
    {
        $request->validate([
            'staff_id' => 'required',
            'week_start' => 'required|date',
            'week_end' => 'required|date',
        ]);

        DB::table('staff_ledgers')->insert([
            'admin_or_user_id' => Auth::id(),
            'saleman_id' => $request->staff_id,
            'week_start' => $request->week_start,
            'week_end' => $request->week_end,
            'weekly_amount' => $request->weekly_amount ?? 0,
            'paid' => $request->paid ?? 0,
            'advance' => $request->advance ?? 0,
            'balance' => $request->balance ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function fetchReceivableReport(Request $request)
    {
        $cities = $request->city ?? [];  // multiple cities array
        $areas = $request->area;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $salesman = $request->salesman;

        if (in_array('All', (array) $cities)) {
            $customerCities = DB::table('customers')->select('city')->distinct()->pluck('city')->toArray();
            $distributorCities = DB::table('distributors')->select('City')->distinct()->pluck('City')->toArray();
            $allCities = array_unique(array_merge($customerCities, $distributorCities));
        } else {
            $allCities = (array) $cities; // multiple cities handle
        }

        $result = [];

        foreach ($allCities as $c) {
            // ==== CUSTOMERS ====
            $customersQuery = DB::table('customers')->where('city', $c);

            if (! empty($areas) && ! in_array('All', (array) $areas)) {
                $customersQuery->whereIn('area', (array) $areas);
            }

            $customers = $customersQuery->get();
            $customerData = [];

            foreach ($customers as $customer) {
                $customerHasSalesBySalesman = DB::table('local_sales')
                    ->where('customer_id', $customer->id)
                    ->when($salesman !== 'All', fn ($query) => $query->where('Saleman', $salesman))
                    ->exists();

                if ($salesman !== 'All' && ! $customerHasSalesBySalesman) {
                    continue;
                }

                $ledger = DB::table('customer_ledgers')->where('customer_id', $customer->id)->first();
                $openingBalance = $ledger->opening_balance ?? 0;

                $totalSales = DB::table('local_sales')
                    ->where('customer_id', $customer->id)
                    ->whereBetween('Date', [$startDate, $endDate])
                    ->when($salesman !== 'All', fn ($query) => $query->where('Saleman', $salesman))
                    ->sum('grand_total');

                $totalReturns = DB::table('sale_returns')
                    ->where('sale_type', 'customer')
                    ->where('party_id', $customer->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->when($salesman !== 'All', function ($query) use ($salesman) {
                        return $query->whereExists(function ($subquery) use ($salesman) {
                            $subquery->select(DB::raw(1))
                                ->from('local_sales')
                                ->whereColumn('local_sales.customer_id', 'sale_returns.party_id')
                                ->where('local_sales.Saleman', $salesman);
                        });
                    })
                    ->sum('total_return_amount');

                $totalRecoveries = DB::table('customer_recoveries as cr')
                    ->join('customer_ledgers as cl', 'cl.id', '=', 'cr.customer_ledger_id')
                    ->where('cl.customer_id', $customer->id)
                    ->when($salesman !== 'All', fn ($q) => $q->where('cr.salesman', $salesman))
                    ->whereBetween('cr.date', [$startDate, $endDate])
                    ->sum('cr.amount_paid');

                $balance = ($openingBalance + $totalSales - $totalReturns) - $totalRecoveries;

                if (round($balance, 2) != 0 || $totalSales > 0 || $totalReturns > 0 || $totalRecoveries > 0) {
                    $customerData[] = [
                        'type' => 'customer',
                        'pcode' => $customer->id,
                        'name' => $customer->customer_name,
                        'shopname' => $customer->shop_name,
                        'address' => $customer->area,
                        'contact' => $customer->phone_number,
                        'balance' => round($balance, 2),
                    ];
                }
            }

            // ==== DISTRIBUTORS ====
            $distributorQuery = DB::table('distributors')->where('City', $c);

            if (! empty($areas) && ! in_array('All', (array) $areas)) {
                $distributorQuery->whereIn('Area', (array) $areas);
            }

            if ($salesman !== 'All') {
                $distributorQuery->whereExists(function ($query) use ($salesman) {
                    $query->select(DB::raw(1))
                        ->from('sales')
                        ->whereColumn('sales.distributor_id', 'distributors.id')
                        ->where('sales.Saleman', $salesman);
                });
            }

            $distributors = $distributorQuery->get();
            $distributorData = [];

            foreach ($distributors as $distributor) {
                $distributorHasSalesBySalesman = DB::table('sales')
                    ->where('distributor_id', $distributor->id)
                    ->when($salesman !== 'All', fn ($query) => $query->where('Saleman', $salesman))
                    ->exists();

                if ($salesman !== 'All' && ! $distributorHasSalesBySalesman) {
                    continue;
                }

                $ledger = DB::table('distributor_ledgers')->where('distributor_id', $distributor->id)->first();
                $openingBalance = $ledger->opening_balance ?? 0;

                $totalSales = DB::table('sales')
                    ->where('distributor_id', $distributor->id)
                    ->whereBetween('Date', [$startDate, $endDate])
                    ->when($salesman !== 'All', fn ($query) => $query->where('Saleman', $salesman))
                    ->sum('grand_total');

                $totalReturns = DB::table('sale_returns')
                    ->where('sale_type', 'distributor')
                    ->where('party_id', $distributor->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->when($salesman !== 'All', function ($query) use ($salesman) {
                        return $query->whereExists(function ($subquery) use ($salesman) {
                            $subquery->select(DB::raw(1))
                                ->from('sales')
                                ->whereColumn('sales.distributor_id', 'sale_returns.party_id')
                                ->where('sales.Saleman', $salesman);
                        });
                    })
                    ->sum('total_return_amount');

                $totalRecoveries = DB::table('recoveries')
                    ->where('distributor_ledger_id', $distributor->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->when($salesman !== 'All', fn ($query) => $query->where('salesman', $salesman))
                    ->sum('amount_paid');

                $balance = ($openingBalance + $totalSales - $totalReturns) - $totalRecoveries;

                if (round($balance, 2) != 0 || $totalSales > 0 || $totalReturns > 0 || $totalRecoveries > 0) {
                    $distributorData[] = [
                        'type' => 'distributor',
                        'pcode' => $distributor->id,
                        'name' => $distributor->Customer,
                        'address' => $distributor->Area,
                        'contact' => $distributor->Contact,
                        'balance' => round($balance, 2),
                    ];
                }
            }

            $result[$c] = [
                'distributors' => $distributorData,
                'customers' => $customerData,
            ];
        }

        return response()->json([
            'data' => $result,
            'salesman_name' => ($salesman !== 'All') ? $salesman : 'All Salesmen',
        ]);
    }

    public function Area_wise_salesman_market_payments()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Customers = Customer::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure
            $cities = City::all(); // Updated the variable name to avoid confusion

            $Salesmans = Salesman::where('admin_or_user_id', $userId)
                ->where('designation', 'Saleman')
                ->get();

            return view('admin_panel.reports.Area_wise_salesman_market_payments', [
                'Customers' => $Customers,
                'cities' => $cities,
                'Salesmans' => $Salesmans,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function receivablesalesmanmarketreport(Request $request)
    {
        $salesmanFilter = $request->input('salesman');
        $city = $request->input('city');
        $areas = $request->input('area');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $result = [];

        // Get all salesmen or specific one
        $salesmen = ($salesmanFilter === 'All')
            ? Salesman::where('designation', 'Saleman')->pluck('name')
            : collect([$salesmanFilter]);

        foreach ($salesmen as $salesman) {
            $salesmanData = [];

            $customers = DB::table('customers')
                ->join('local_sales', 'customers.id', '=', 'local_sales.customer_id')
                ->where('local_sales.Saleman', $salesman)
                ->where('customers.identify', 'admin') // Or your dynamic identity logic
                ->when($city !== 'All', function ($q) use ($city) {
                    $q->where('customers.city', $city);
                })
                ->when(! empty($areas) && $city !== 'All', function ($q) use ($areas) {
                    $q->whereIn('customers.area', $areas);
                })
                ->select('customers.*')
                ->distinct()
                ->get();

            foreach ($customers as $customer) {
                // Get latest ledger
                $ledger = DB::table('customer_ledgers')
                    ->where('customer_id', $customer->id)
                    ->latest('created_at')
                    ->first();

                $openingBalance = $ledger->opening_balance ?? 0;
                $previousBalance = $ledger->previous_balance ?? 0;
                $closingBalance = $ledger->closing_balance ?? 0;

                $totalSales = DB::table('local_sales')
                    ->where('customer_id', $customer->id)
                    ->where('Saleman', $salesman)
                    ->whereBetween('Date', [$startDate, $endDate])
                    ->sum('grand_total');

                $totalReturns = DB::table('sale_returns')
                    ->where('sale_type', 'customer')
                    ->where('party_id', $customer->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('total_return_amount');

                $totalRecoveries = DB::table('customer_recoveries as cr')
                    ->join('customer_ledgers as cl', 'cl.id', '=', 'cr.customer_ledger_id')
                    ->where('cl.customer_id', $customer->id)
                    ->where('cr.salesman', $salesman)
                    ->whereBetween('cr.date', [$startDate, $endDate])
                    ->sum('cr.amount_paid');

                $balance = ($openingBalance + $totalSales - $totalReturns) - $totalRecoveries;

                // Group by city > area
                $salesmanData[$customer->city][$customer->area][] = [
                    'customer_name' => $customer->customer_name,
                    'shop_name' => $customer->shop_name,
                    'phone' => $customer->phone_number,
                    'opening_balance' => round($openingBalance, 2),
                    'previous_balance' => round($previousBalance, 2),
                    'closing_balance' => round($closingBalance, 2),
                    'total_sales' => round($totalSales, 2),
                    'total_returns' => round($totalReturns, 2),
                    'total_recoveries' => round($totalRecoveries, 2),
                    'balance' => round($balance, 2),
                ];
            }

            $result[$salesman] = $salesmanData;
        }

        return response()->json($result);
    }

    public function Date_wise_Sales_Report()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Customers = Customer::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure

            $Salesmans = Salesman::where('admin_or_user_id', $userId)
                ->where('designation', 'Saleman')
                ->get();

            return view('admin_panel.reports.Date_wise_Sales_Report', [
                'Customers' => $Customers,
                'Salesmans' => $Salesmans,
            ]);
        } else {
            return redirect()->back();
        }
    }
    // public function getsalesreport(Request $request)
    // {
    //     $salesman = $request->salesman;
    //     $type = $request->type;
    //     $startDate = $request->start_date;
    //     $endDate = $request->end_date;

    //     $sales = [];

    //     // --- Distributor Sales ---
    //     if ($type == 'all' || $type == 'distributor') {
    //         $query = DB::table('sales')->whereBetween('Date', [$startDate, $endDate]);

    //         if ($salesman !== 'All') {
    //             $query->where('Saleman', $salesman);
    //         }

    //         $results = $query->get();

    //         $distributorIds = $results->pluck('distributor_id')->unique()->filter()->values();

    //         $distributorMap = DB::table('distributors')
    //             ->whereIn('id', $distributorIds)
    //             ->pluck('Customer', 'id');

    //         foreach ($results as $row) {
    //             $items = json_decode($row->cart_items, true) ?? [];
    //             $cartons = json_decode($row->carton_qty, true) ?? [];
    //             $pcs = json_decode($row->pcs, true) ?? [];

    //             $itemDetails = [];
    //             foreach ($items as $i => $itm) {
    //                 $itemDetails[] = $itm . " (" . ($cartons[$i] ?? 0) . " CTN, " . ($pcs[$i] ?? 0) . " PCS)";
    //             }

    //             $distributorName = $distributorMap[$row->distributor_id] ?? 'Distributor-' . $row->distributor_id;

    //             $sales[] = [
    //                 'invoice_number' => $row->invoice_number,
    //                 'date' => $row->Date,
    //                 'party_name' => $distributorName,
    //                 'area' => $row->distributor_area ?? 'N/A',
    //                 'remarks' => 'Distributor Sale',
    //                 'items' => implode(", ", $itemDetails),
    //                 'amount_paid' => number_format($row->net_amount),
    //                 'salesman' => $row->Saleman ?? '-'
    //             ];
    //         }
    //     }

    //     // --- Customer Sales ---
    //     if ($type == 'all' || $type == 'customer') {
    //         $query = DB::table('local_sales')->whereBetween('Date', [$startDate, $endDate]);

    //         if ($salesman !== 'All') {
    //             $query->where('Saleman', $salesman);
    //         }

    //         $results = $query->get();

    //         foreach ($results as $row) {
    //             $items = json_decode($row->cart_items, true) ?? [];
    //             $cartons = json_decode($row->carton_qty, true) ?? [];
    //             $pcs = json_decode($row->pcs, true) ?? [];

    //             $itemDetails = [];
    //             foreach ($items as $i => $itm) {
    //                 $itemDetails[] = $itm . " (" . ($cartons[$i] ?? 0) . " CTN, " . ($pcs[$i] ?? 0) . " PCS)";
    //             }

    //             $sales[] = [
    //                 'invoice_number' => $row->invoice_number,
    //                 'date' => $row->Date,
    //                 'party_name' => $row->customer_shopname ?? 'Customer-' . $row->customer_id,
    //                 'area' => $row->customer_area ?? 'N/A',
    //                 'remarks' => 'Customer Sale',
    //                 'items' => implode(", ", $itemDetails),
    //                 'amount_paid' => number_format($row->net_amount),
    //                 'salesman' => $row->Saleman ?? '-'
    //             ];
    //         }
    //     }

    //     return response()->json($sales);
    // }

    public function getsalesreport(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        // If no dates provided, use today's date
        if (empty($startDate) || empty($endDate)) {
            $startDate = Carbon::today()->startOfDay();
            $endDate = Carbon::today()->endOfDay();
        }

        $results = DB::table('local_sales')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $sales = [];

        foreach ($results as $row) {
            $items = json_decode($row->cart_items, true) ?? [];
            $cartons = json_decode($row->carton_qty, true) ?? [];
            $pcs = json_decode($row->pcs, true) ?? [];

            $itemDetails = [];
            foreach ($items as $i => $itm) {
                $itemDetails[] = $itm.' ('.($cartons[$i] ?? 0).' CTN, '.($pcs[$i] ?? 0).' PCS)';
            }

            $sales[] = [
                'invoice_number' => $row->invoice_number,
                'date' => $row->Date,
                'party_name' => $row->customer_shopname ?? 'Customer-'.$row->customer_id,
                'area' => $row->customer_area ?? 'N/A',
                'remarks' => 'Local Sale',
                'items' => implode(', ', $itemDetails),
                'amount_paid' => number_format($row->net_amount),
            ];
        }

        return response()->json($sales);
    }

    public function Product_wise_Sales_Report()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Products = Product::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure

            return view('admin_panel.reports.Product_wise_Sales_Report', [
                'Products' => $Products,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function getProductsalesreport(Request $request)
    {
        $products = $request->Product;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        if (empty($startDate) || empty($endDate)) {
            $startDate = \Carbon\Carbon::today()->startOfDay();
            $endDate = \Carbon\Carbon::today()->endOfDay();
        } else {
            $startDate = \Carbon\Carbon::parse($startDate)->startOfDay();
            $endDate = \Carbon\Carbon::parse($endDate)->endOfDay();
        }

        $sales = [];

        // --- Distributor Sales ---
        $query = DB::table('sales')->whereBetween('created_at', [$startDate, $endDate]);
        $results = $query->get();

        foreach ($results as $row) {
            $items = json_decode($row->item, true) ?? [];
            $cartons = json_decode($row->carton_qty, true) ?? [];
            $pcs = json_decode($row->pcs_qty, true) ?? [];
            $amounts = json_decode($row->amount, true) ?? [];
            $liters = json_decode($row->liter, true) ?? [];

            foreach ($items as $i => $itm) {
                if (empty($itm) || strtolower($itm) == 'select item') {
                    continue;
                }

                if (!in_array('All', $products) && !in_array($itm, $products)) {
                    continue;
                }

                if (!isset($sales[$itm])) {
                    $sales[$itm] = [
                        'item' => $itm,
                        'carton_qty' => 0,
                        'pcs' => 0,
                        'liters' => 0,
                        'amount' => 0,
                    ];
                }

                $sales[$itm]['carton_qty'] += (float)($cartons[$i] ?? 0);
                $sales[$itm]['pcs'] += (float)($pcs[$i] ?? 0);
                $sales[$itm]['liters'] += (float)($liters[$i] ?? 0);
                $sales[$itm]['amount'] += (float)($amounts[$i] ?? 0);
            }
        }

        // --- Customer Sales (Local Sales) ---
        $query = DB::table('local_sales')->whereBetween('created_at', [$startDate, $endDate]);
        $results = $query->get();

        foreach ($results as $row) {
            $items = json_decode($row->item, true) ?? [];
            $qtys = json_decode($row->qty, true) ?? [];
            $amounts = json_decode($row->amount, true) ?? [];

            foreach ($items as $i => $itm) {
                if (empty($itm) || strtolower($itm) == 'select item') {
                    continue;
                }

                if (!in_array('All', $products) && !in_array($itm, $products)) {
                    continue;
                }

                if (!isset($sales[$itm])) {
                    $sales[$itm] = [
                        'item' => $itm,
                        'carton_qty' => 0,
                        'pcs' => 0,
                        'liters' => 0,
                        'amount' => 0,
                    ];
                }

                // local_sales records qty which we can treat as pcs or carton_qty. Let's add to carton_qty/pcs based on unit or default to carton_qty
                $sales[$itm]['carton_qty'] += (float)($qtys[$i] ?? 0);
                $sales[$itm]['amount'] += (float)($amounts[$i] ?? 0);
            }
        }

        return response()->json(array_values($sales));
    }
    // public function revenue_Record()
    // {
    //     // Totals
    //     $totalSale = 0;
    //     $totalCost = 0;
    //     $totalProfit = 0;
    //     $totalUnitProfit = 0;

    //     $productSummary = [];

    //     // Fetch all sales
    //     $sales = LocalSale::all();

    //     foreach ($sales as $sale) {
    //         $items = json_decode($sale->item, true);
    //         $codes = json_decode($sale->code, true);
    //         $amounts = json_decode($sale->amount, true);
    //         $quantities = json_decode($sale->pcs, true);
    //         $returns = json_decode($sale->return_quantity ?? '[]', true);

    //         if ($items && is_array($items)) {
    //             foreach ($items as $index => $itemName) {
    //                 $productId = $codes[$index] ?? null;
    //                 $saleAmount = $amounts[$index] ?? 0;
    //                 $quantity = $quantities[$index] ?? 0;
    //                 $returnQty = $returns[$index] ?? 0;

    //                 $product = Product::find($productId);
    //                 if (!$product) continue;

    //                 $wholesalePrice = $product->wholesale_price ?? 0;
    //                 $retailPrice = $product->retail_price ?? 0;

    //                 $cost = ($wholesalePrice * $quantity) - ($wholesalePrice * $returnQty);
    //                 $adjustedSaleAmount = $saleAmount - ($retailPrice * $returnQty);
    //                 $profit = $adjustedSaleAmount - $cost;

    //                 $totalSale += $adjustedSaleAmount;
    //                 $totalCost += $cost;
    //                 $totalProfit += $profit;

    //                 if (!isset($productSummary[$productId])) {
    //                     $productSummary[$productId] = [
    //                         'name' => $product->item_name,
    //                         'category' => $product->category,
    //                         'sub_category' => $product->sub_category,
    //                         'quantity' => 0,
    //                         'unit_wholesale' => $wholesalePrice,
    //                         'unit_retail' => $retailPrice,
    //                         'total_sale' => 0,
    //                         'total_cost' => 0,
    //                         'profit' => 0,
    //                         'total_return' => 0,
    //                     ];
    //                 }

    //                 $productSummary[$productId]['quantity'] += $quantity;
    //                 $productSummary[$productId]['total_return'] += $returnQty;
    //                 $productSummary[$productId]['total_sale'] += $adjustedSaleAmount;
    //                 $productSummary[$productId]['total_cost'] += $cost;
    //                 $productSummary[$productId]['profit'] += $profit;
    //             }
    //         }
    //     }

    //     // ✅ Total Unit Profit (sum of each product's unit profit)
    //     foreach ($productSummary as $product) {
    //         $unitProfit = $product['unit_retail'] - $product['unit_wholesale'];
    //         $totalUnitProfit += $unitProfit;
    //     }

    //     $netProfit = $totalProfit;

    //     return view('admin_panel.reports.revenue_record', compact(
    //         'totalSale', 'totalCost', 'totalProfit', 'netProfit', 'productSummary', 'totalUnitProfit'
    //     ));
    // }

    public function vendorStockRecord()
    {
        if (! Auth::id()) {
            return redirect()->back();
        }

        $userId = Auth::id();

        $categories = Category::where('admin_or_user_id', $userId)->get();
        $vendors = DB::table('vendors')
            ->where('admin_or_user_id', $userId)
            ->select('id', 'Party_code', 'Party_name')
            ->orderBy('Party_name')
            ->get();

        return view('admin_panel.reports.stock_Record_vendor', [
            'categories' => $categories,
            'vendors' => $vendors,
        ]);
    }

    // Items by subcategory (same as before, vendor-agnostic)
    public function getVendorItems($subcategory)
    {
        $items = Product::where('sub_category', $subcategory)
            ->select('item_code', 'item_name')->get();

        return response()->json($items);
    }

    // ===== Helper to sum cartons from JSON arrays for a matched $needle item =====
    private function sumFromJsonRows($rows, $itemCol, $qtyCol, $needle)
    {
        $sum = 0;
        foreach ($rows as $row) {
            $items = json_decode($row->$itemCol, true);
            $qtys = json_decode($row->$qtyCol, true);

            if (is_array($items) && is_array($qtys)) {
                foreach ($items as $i => $name) {
                    if ($name === $needle) {
                        $sum += isset($qtys[$i]) ? intval($qtys[$i]) : 0;
                    }
                }
            }
        }

        return $sum;
    }

    // ===== Helper to sum "pcs to liters" if ever needed (we're using cartons here) =====
    private function litersFor(Product $p, $cartons)
    {
        // size like "120ml" or "1 liter"
        $sizeText = strtolower(trim($p->size ?? ''));
        $perPieceL = 0;

        if (str_contains($sizeText, 'ml')) {
            $num = floatval(preg_replace('/[^0-9.]/', '', $sizeText));
            $perPieceL = $num / 1000.0;
        } elseif (str_contains($sizeText, 'liter') || preg_match('/\b\d+(\.\d+)?l\b/', $sizeText)) {
            $num = floatval(preg_replace('/[^0-9.]/', '', $sizeText));
            $perPieceL = $num;
        } else {
            $perPieceL = floatval($sizeText) ?: 0;
        }

        $pcsPerCarton = intval($p->pcs_in_carton ?: 0);

        return $perPieceL * $pcsPerCarton * $cartons;
    }

    // ===== MAIN: Vendor-wise filtered data =====
    public function getVendorItemDetailsVendorWise(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'category' => 'required',
            'subcategory' => 'required',
            'itemCode' => 'required',
        ]);

        $userId = Auth::id();
        $vendorId = (int) $request->vendor_id;

        $vendor = DB::table('vendors')
            ->where('admin_or_user_id', $userId)
            ->where('id', $vendorId)
            ->first();

        if (! $vendor) {
            return response()->json([]);
        }

        $vendorPartyCode = $vendor->Party_code;

        /* ========= 1️⃣ GET ONLY VENDOR PURCHASED ITEMS ========= */
        $vendorItemNames = Purchase::where('admin_or_user_id', $userId)
            ->where('party_code', $vendorPartyCode)
            ->pluck('item')
            ->toArray();

        $allowedItems = [];

        foreach ($vendorItemNames as $json) {
            $items = json_decode($json, true) ?? [];
            foreach ($items as $it) {
                $allowedItems[] = trim($it);
            }
        }

        $allowedItems = array_unique($allowedItems);

        if (empty($allowedItems)) {
            return response()->json([]);
        }

        /* ========= 2️⃣ PRODUCTS (ONLY THOSE ITEMS) ========= */
        $q = Product::where('admin_or_user_id', $userId)
            ->whereIn('item_name', $allowedItems);

        if ($request->category !== 'all') {
            $q->where('category', $request->category);
        }
        if ($request->subcategory !== 'all') {
            $q->where('sub_category', $request->subcategory);
        }
        if ($request->itemCode !== 'all') {
            $q->where('item_code', $request->itemCode);
        }

        $products = $q->get();
        $result = [];

        foreach ($products as $p) {

            $pcsInCarton = max((int) $p->pcs_in_carton, 1);
            $itemName = $p->item_name;

            /* ========= PURCHASE (VENDOR ONLY) ========= */
            $purchasePCS = 0;

            $purchases = Purchase::where('admin_or_user_id', $userId)
                ->where('party_code', $vendorPartyCode)
                ->whereJsonContains('item', $itemName)
                ->get();

            foreach ($purchases as $pur) {
                $names = json_decode($pur->item, true) ?? [];
                $cartons = json_decode($pur->carton_qty, true) ?? [];
                $pcs = json_decode($pur->pcs ?? '[]', true) ?? [];

                foreach ($names as $i => $n) {
                    if ($n === $itemName) {
                        $purchasePCS +=
                            ((int) ($cartons[$i] ?? 0) * $pcsInCarton)
                            + (int) ($pcs[$i] ?? 0);
                    }
                }
            }

            /* ========= PURCHASE RETURN ========= */
            $purchaseReturnPCS = 0;

            $pReturns = DB::table('purchase_returns')
                ->where('admin_or_user_id', $userId)
                ->whereJsonContains('item', $itemName)
                ->get();

            foreach ($pReturns as $pr) {
                $names = json_decode($pr->item, true) ?? [];
                $cartons = json_decode($pr->carton_qty ?? '[]', true) ?? [];
                $pcs = json_decode($pr->return_qty ?? '[]', true) ?? [];

                foreach ($names as $i => $n) {
                    if ($n === $itemName) {
                        $purchaseReturnPCS +=
                            ((int) ($cartons[$i] ?? 0) * $pcsInCarton)
                            + (int) ($pcs[$i] ?? 0);
                    }
                }
            }

            $soldPCS = 0;

            $sales = LocalSale::where('admin_or_user_id', $userId)
                ->whereJsonContains('item', $itemName)
                ->get();

            foreach ($sales as $sale) {
                $names = json_decode($sale->item, true) ?? [];
                $pcs = json_decode($sale->pcs, true) ?? [];

                foreach ($names as $i => $n) {
                    if ($n === $itemName) {
                        $soldPCS += (int) ($pcs[$i] ?? 0);
                    }
                }
            }

            $returnPCS = 0;

            $returns = DB::table('sale_returns')
                ->where('admin_or_user_id', $userId)
                ->where('sale_type', 'customer')
                ->where('item_names', $itemName)
                ->get();

            foreach ($returns as $r) {
                $returnPCS += ((int) $r->pcs_qty > 0)
                    ? (int) $r->pcs_qty
                    : ((int) $r->carton_qty * $pcsInCarton);
            }

            $balanceStock = (int) ($p->initial_stock ?? 0);
            $perPcsPrice = $p->wholesale_price / $pcsInCarton;
            $stockValue = round($perPcsPrice * $balanceStock, 2);

            $result[] = [
                'vendor_name' => $vendor->Party_name,
                'item_code' => $p->item_code,
                'item_name' => $itemName,
                'size' => $p->size,
                'pcs_in_carton' => $pcsInCarton,

                'opening_stock' => $balanceStock,

                'purchased_qty' => $purchasePCS,
                'purchase_return_qty' => $purchaseReturnPCS,

                'sold_local_qty' => $soldPCS,
                'return_local_qty' => $returnPCS,

                'balance_stock' => $balanceStock,
                'w_price' => $p->wholesale_price,
                'stock_value' => $stockValue,
            ];
        }

        return response()->json($result);
    }
}
