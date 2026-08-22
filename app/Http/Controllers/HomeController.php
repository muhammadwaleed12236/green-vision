<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\LocalSale;
use App\Models\Account;
use App\Models\JournalVoucher;
use App\Models\CashBook;

class HomeController extends Controller
{
    public function index()
    {
        if (Auth::id()) {
            $usertype = Auth()->user()->usertype;
            $userId = Auth::id();

            if ($usertype == 'distributor') {
                return view('distributor_panel.dashboard', [
                    'userId' => $userId,
                ]);
            } elseif ($usertype == 'local_salesman' || $usertype == 'salesman') {
                return view('salesman_panel.dashboard', [
                    'userId' => $userId,
                ]);
            } else {
                // Default to admin dashboard for 'admin', 'user' and other administrative roles
                $stats = $this->getAdminStats();
                
                $accounts = Account::with('category')->where('status', true)->orderBy('name')->get();

                return view('admin_panel.dashboard', compact('stats', 'userId', 'accounts'));
            }
        }
    }

    private function getAdminStats()
    {
        // Total Purchase Due
        $totalPurchaseDue = \App\Models\VendorLedger::sum('closing_balance');

        // Total Sales Due
        $totalSalesDue = \App\Models\CustomerLedger::sum('closing_balance');

        // Total Sale Revenue (Net Amount AFTER discount - EXCLUDING DELETED & ESTIMATES)
        $totalSaleAmount = LocalSale::where('sale_type', '!=', 'estimate')->sum('net_amount');

        // Total Stock Investment
        $totalStockInvestment = \App\Models\Purchase::sum('grand_total');

        // Total Contractor Costs (from job_orders table - contractor and vendor assignments)
        $totalContractorCosts = \App\Models\JobOrder::whereIn('assignee_type', ['contractor', 'vendor'])
            ->whereNull('deleted_at')
            ->sum('total_amount');

        // Total Job Costs (same as contractor costs for backward compatibility)
        $totalJobCosts = $totalContractorCosts;

        // Total Other Expenses (EXCLUDING job assignment expenses)
        $totalExpenses = \App\Models\AddExpense::whereHas('expense', function($query) {
                $query->where('expense_name', 'NOT LIKE', '%Job Assignment%');
            })
            ->sum('amount');


        // Payment In & Out
        $todayPaymentIn = JournalVoucher::where('voucher_type', 'receipt')->whereDate('voucher_date', now()->toDateString())->sum('credit_amount') 
            + CashBook::whereDate('date', now()->toDateString())->sum('debit');
        
        $overallReceived = JournalVoucher::where('voucher_type', 'receipt')->sum('credit_amount') 
            + CashBook::sum('debit');

        $todayPaymentOut = JournalVoucher::where('voucher_type', 'payment')->whereDate('voucher_date', now()->toDateString())->sum('debit_amount') 
            + CashBook::whereDate('date', now()->toDateString())->sum('credit');

        $overallSettled = JournalVoucher::where('voucher_type', 'payment')->sum('debit_amount') 
            + CashBook::sum('credit');

        // Today's Sales & Purchases (Excluding Estimates)
        $todaySales = LocalSale::where('sale_type', '!=', 'estimate')->whereDate('created_at', now()->toDateString())->sum('net_amount');
        $todayPurchases = \App\Models\Purchase::whereDate('created_at', now()->toDateString())->sum('grand_total');

        // Counts
        $customersCount = \App\Models\Customer::count();
        $vendorsCount = \App\Models\Vendor::count();
        $purchaseInvoiceCount = \App\Models\Purchase::count();
        $local_salesInvoiceCount = LocalSale::where('sale_type', '!=', 'estimate')->count();
        $productsCount = \App\Models\Product::count();
        $staffCount = \App\Models\Salesman::count();

        // Monthly Sales & Purchases (Last 12 Months) - Using net_amount for accurate revenue (Excluding Estimates)
        $rawMonthlySales = DB::table('local_sales')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('SUM(net_amount) as total')
            )
            ->whereNull('deleted_at')
            ->where('sale_type', '!=', 'estimate')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();


        $rawMonthlyPurchases = DB::table('purchases')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('SUM(grand_total) as total')
            )
            ->whereNull('deleted_at')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Align the monthly sales and purchases so they match month-by-month
        $monthsList = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $year = (int)$date->format('Y');
            $month = (int)$date->format('n');
            $key = "{$year}-{$month}";
            $monthsList[$key] = [
                'month' => $month,
                'year' => $year,
                'sales' => 0.0,
                'purchases' => 0.0
            ];
        }

        foreach ($rawMonthlySales as $ms) {
            $key = "{$ms->year}-{$ms->month}";
            if (isset($monthsList[$key])) {
                $monthsList[$key]['sales'] = (float)$ms->total;
            }
        }

        foreach ($rawMonthlyPurchases as $mp) {
            $key = "{$mp->year}-{$mp->month}";
            if (isset($monthsList[$key])) {
                $monthsList[$key]['purchases'] = (float)$mp->total;
            }
        }

        $monthlylocal_sales = [];
        $monthlyPurchases = [];
        foreach ($monthsList as $data) {
            $monthlylocal_sales[] = (object)[
                'month' => $data['month'],
                'year' => $data['year'],
                'total' => $data['sales']
            ];
            $monthlyPurchases[] = (object)[
                'month' => $data['month'],
                'year' => $data['year'],
                'total' => $data['purchases']
            ];
        }

        // =========================
        // Top Selling Items & Products
        // =========================
        $allSales = LocalSale::whereNull('deleted_at')->where('sale_type', '!=', 'estimate')->get();
        $totals = [];

        foreach ($allSales as $sale) {
            $items = is_array($sale->item) ? $sale->item : json_decode($sale->item, true);
            $amounts = is_array($sale->amount) ? $sale->amount : json_decode($sale->amount, true);
            $qtys = is_array($sale->qty) ? $sale->qty : json_decode($sale->qty, true);

            if (!is_array($items)) continue;

            foreach ($items as $index => $itemName) {
                if (empty($itemName)) continue;

                if (!isset($totals[$itemName])) {
                    $totals[$itemName] = [
                        'total_sales' => 0.0,
                        'total_qty' => 0.0
                    ];
                }

                if (is_array($amounts) && isset($amounts[$index])) {
                    $cleanedAmount = str_replace(',', '', (string)$amounts[$index]);
                    if (is_numeric($cleanedAmount)) {
                        $totals[$itemName]['total_sales'] += (float)$cleanedAmount;
                    }
                }

                // Optionally sum quantity if qty array exists
                if (is_array($qtys) && isset($qtys[$index])) {
                    $cleanedQty = str_replace(',', '', (string)$qtys[$index]);
                    if (is_numeric($cleanedQty)) {
                        $totals[$itemName]['total_qty'] += (float)$cleanedQty;
                    }
                }
            }
        }

        // Sort by total_sales descending
        uasort($totals, function ($a, $b) {
            return $b['total_sales'] <=> $a['total_sales'];
        });

        // Top 6 Selling Items
        $topSellingItems = collect(array_slice($totals, 0, 6, true))->map(function ($data, $itemName) {
            return [
                'item_name' => $itemName,
                'total_sales' => $data['total_sales'],
                'total_qty' => $data['total_qty'],
            ];
        });

        // Top Products for chart (top 5)
        $topProducts = $topSellingItems->take(5)->values();

        // =========================
        // Recent Sales
        // =========================
        $recentlocal_sales = LocalSale::leftJoin('customers', 'local_sales.customer_id', '=', 'customers.id')
            ->where('local_sales.sale_type', '!=', 'estimate')
            ->select('local_sales.*', DB::raw('COALESCE(customers.customer_name, local_sales.customer_shopname, "Walk-in Customer") as customer_name'))
            ->orderBy('local_sales.id', 'desc')
            ->limit(10)
            ->get();

        // =========================
        // Payment Status
        // =========================
        $paymentStatus = [
            'paid' => LocalSale::where('sale_type', '!=', 'estimate')->where('job_status', 'paid')->count(),
            'unpaid' => LocalSale::where('sale_type', '!=', 'estimate')->where('job_status', 'unpaid')->count(),
            'pending' => LocalSale::where('sale_type', '!=', 'estimate')->where('job_status', 'pending')->count(),
        ];

        // =========================
        // Net Profit
        // =========================
        $netProfit = $totalSaleAmount - $totalStockInvestment - $totalJobCosts - $totalExpenses;

        return [
            'totalPurchaseDue' => $totalPurchaseDue,
            'totalSalesDue' => $totalSalesDue,
            'totalSaleAmount' => $totalSaleAmount,
            'totalStockInvestment' => $totalStockInvestment,
            'totalJobCosts' => $totalJobCosts,
            'totalContractorCosts' => $totalContractorCosts,
            'totalExpenses' => $totalExpenses,
            'netProfit' => $netProfit,
            
            'todayPaymentIn' => $todayPaymentIn,
            'overallReceived' => $overallReceived,
            'todayPaymentOut' => $todayPaymentOut,
            'overallSettled' => $overallSettled,
            'todaySales' => $todaySales,
            'todayPurchases' => $todayPurchases,

            'customersCount' => $customersCount,
            'vendorsCount' => $vendorsCount,
            'purchaseInvoiceCount' => $purchaseInvoiceCount,
            'local_salesInvoiceCount' => $local_salesInvoiceCount,
            'productsCount' => $productsCount,
            'staffCount' => $staffCount,
            'monthlylocal_sales' => $monthlylocal_sales,
            'monthlyPurchases' => $monthlyPurchases,
            'topProducts' => $topProducts,
            'recentlocal_sales' => $recentlocal_sales,
            'topSellingItems' => $topSellingItems,
            'paymentStatus' => $paymentStatus,
        ];
    }
}
