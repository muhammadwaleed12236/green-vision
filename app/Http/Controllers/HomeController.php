<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            } elseif ($usertype == 'admin') {
                // Dashboard Statistics
                $stats = $this->getAdminStats();

                // dd($stats);
                return view('admin_panel.dashboard', compact('stats', 'userId'));
            } elseif ($usertype == 'local_salesman') {
                return view('local_salesman_panel.dashboard', [
                    'userId' => $userId,
                ]);
            } else {
                return redirect()->back();
            }
        }
    }

    private function getAdminStats()
    {
        // Total Purchase Due
        $totalPurchaseDue = DB::table('purchases')
            ->sum(DB::raw('grand_total - gross_total'));

        // Total local_sales Due
        $totallocal_salesDue = DB::table('local_sales')
            ->sum(DB::raw('grand_total - net_amount'));

        //         $totallocal_salesDue = DB::table('local_sales')
        // ->whereRaw('net_amount > grand_total')
        // ->sum(DB::raw('net_amount - grand_total'));

        // Total Sale Amount
        $totalSaleAmount = DB::table('local_sales')->sum('net_amount');

        // Total Purchase Amount
        $totalPurchaseAmount = DB::table('purchases')->sum('grand_total');

        // Total Expenses
        $totalExpenses = DB::table('add_expenses')->sum('amount');

        // Counts
        $customersCount = DB::table('customers')->count();
        $vendorsCount = DB::table('vendors')->count();
        $purchaseInvoiceCount = DB::table('purchases')->count();
        $local_salesInvoiceCount = DB::table('local_sales')->count();
        $productsCount = DB::table('products')->count();
        $staffCount = DB::table('sales_mens')->count();

        // Monthly local_sales & Purchase Data (Last 12 Months)
        $monthlylocal_sales = DB::table('local_sales')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('SUM(grand_total) as total')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $monthlyPurchases = DB::table('purchases')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('SUM(gross_total) as total')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Top Selling Products
        $topProducts = DB::table('job_items')
            ->join('products', 'job_items.item_id', '=', 'products.id')
            ->select(
                'products.item_name',
                DB::raw('SUM(job_items.qty) as items_quantity'),
                DB::raw('SUM(job_items.total) as total_revenue')
            )
            ->groupBy('products.id', 'products.item_name')
            ->orderBy('total_revenue', 'desc')
            ->limit(5)
            ->get();

        // Recent local_sales
        $recentlocal_sales = DB::table('local_sales')
            ->join('customers', 'local_sales.customer_id', '=', 'customers.id')
            ->select('local_sales.*', 'customers.customer_name as customer_name')
            ->orderBy('local_sales.created_at', 'desc')
            ->limit(10)
            ->get();

        // Top Selling Items (Simple - No categories needed)
        $topSellingItems = DB::table('job_items')
            ->select(
                'item_name',
                DB::raw('SUM(total) as total_sales')
            )
            ->whereNotNull('item_name')
            ->groupBy('item_name')
            ->orderBy('total_sales', 'desc')
            ->limit(6)
            ->get();

        // Payment Status Distribution
        $paymentStatus = [
            'paid' => DB::table('local_sales')->where('job_status', 'paid')->count(),
            'unpaid' => DB::table('local_sales')->where('job_status', 'unpaid')->count(),
            'pending' => DB::table('local_sales')->where('job_status', 'pending')->count(),
        ];

        // Net Profit Calculation
        $netProfit = $totalSaleAmount - $totalPurchaseAmount - $totalExpenses;

        return [
            'totalPurchaseDue' => $totalPurchaseDue,
            'totallocal_salesDue' => $totallocal_salesDue,
            'totalSaleAmount' => $totalSaleAmount,
            'totalPurchaseAmount' => $totalPurchaseAmount,
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