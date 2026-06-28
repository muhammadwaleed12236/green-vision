<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\LocalSale;

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

                return view('admin_panel.dashboard', compact('stats', 'userId'));
            }
        }
    }

    private function getAdminStats()
    {
        // Total Purchase Due
        $totalPurchaseDue = \App\Models\VendorLedger::sum('closing_balance');

        // Total Sales Due
        $totalSalesDue = \App\Models\CustomerLedger::sum('closing_balance');

        // Total Sale Revenue (Net Amount AFTER discount - EXCLUDING DELETED)
        $totalSaleAmount = LocalSale::sum('net_amount');

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


        // Counts
        $customersCount = \App\Models\Customer::count();
        $vendorsCount = \App\Models\Vendor::count();
        $purchaseInvoiceCount = \App\Models\Purchase::count();
        $local_salesInvoiceCount = LocalSale::count();
        $productsCount = \App\Models\Product::count();
        $staffCount = \App\Models\Salesman::count();

        // Monthly Sales & Purchases (Last 12 Months) - Using net_amount for accurate revenue
        $rawMonthlySales = DB::table('local_sales')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('SUM(net_amount) as total')
            )
            ->whereNull('deleted_at')
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
        $allSales = LocalSale::whereNull('deleted_at')->get();
        $totals = [];

        foreach ($allSales as $sale) {
            $items = json_decode($sale->item, true);       // e.g., ["glass", "almonium"]
            $amounts = json_decode($sale->amount, true);   // e.g., [1500, 10.42]

            if (!$items || !$amounts) continue;

            foreach ($items as $index => $itemName) {
                if (!isset($totals[$itemName])) {
                    $totals[$itemName] = [
                        'total_sales' => 0,
                        'total_qty' => 0
                    ];
                }

                $totals[$itemName]['total_sales'] += $amounts[$index] ?? 0;

                // Optionally sum quantity if qty array exists
                $qtys = json_decode($sale->qty, true); // assuming qty column has array
                $totals[$itemName]['total_qty'] += $qtys[$index] ?? 0;
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
            ->select('local_sales.*', DB::raw('COALESCE(customers.customer_name, local_sales.customer_shopname, "Walk-in Customer") as customer_name'))
            ->orderBy('local_sales.id', 'desc')
            ->limit(10)
            ->get();

        // =========================
        // Payment Status
        // =========================
        $paymentStatus = [
            'paid' => LocalSale::where('job_status', 'paid')->count(),
            'unpaid' => LocalSale::where('job_status', 'unpaid')->count(),
            'pending' => LocalSale::where('job_status', 'pending')->count(),
        ];

        // =========================
        // Net Profit
        // =========================
        $netProfit = $totalSaleAmount - $totalJobCosts - $totalExpenses;

        return [
            'totalPurchaseDue' => $totalPurchaseDue,
            'totalSalesDue' => $totalSalesDue,
            'totalSaleAmount' => $totalSaleAmount,
            'totalStockInvestment' => $totalStockInvestment,
            'totalJobCosts' => $totalJobCosts,
            'totalContractorCosts' => $totalContractorCosts,
            'totalExpenses' => $totalExpenses,
            'netProfit' => $netProfit,

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
