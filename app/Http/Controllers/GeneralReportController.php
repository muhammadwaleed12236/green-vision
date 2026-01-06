<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeneralReportController extends Controller
{
    public function index()
    {
        return view('admin_panel.reports.job_profit_report');
    }

public function fetch(Request $request)
{

    $from = $request->from;
    $to   = $request->to;

    $jobOrders = DB::table('job_orders')
        ->leftJoin('sales_mens', 'sales_mens.id', '=', 'job_orders.staff_id')
        ->whereBetween('job_orders.job_date', [$from, $to])
        ->select(
            'job_orders.*',
            'sales_mens.name as staff_name'
        )
        ->get();
// dd($jobOrders);
    $jobRows = [];
    $totalJobAmount = 0;
    $totalJobProfit = 0;

foreach ($jobOrders as $job) {

    $jobAmount = $job->total_amount ?? 0;

    // 🔹 Job date ke against stock outs nikaalo (SAME AS STOCK OUT REPORT)
    $stockData = DB::table('stock_outs')
        ->whereDate('stock_outs.created_at', $job->job_date)
        ->where('stock_outs.admin_or_user_id', $job->admin_or_user_id)
        ->selectRaw('COUNT(id) as total_items, SUM(total_stock) as total_stock')
        ->first();

    $totalItems = $stockData->total_items ?? 0;
    $stockCost  = $stockData->total_stock ?? 0;

    $jobProfit = $jobAmount - $stockCost;

    $jobRows[] = [
        'job'          => $job->job_order_no,
        'staff'        => $job->staff_name ?? 'N/A',
        'job_amount'   => number_format($jobAmount, 2),
        'total_items'  => $totalItems,
        'stock_cost'   => number_format($stockCost, 2),
        'profit'       => number_format($jobProfit, 2),
    ];

    $totalJobAmount += $jobAmount;
    $totalJobProfit += $jobProfit;
}


    $staffExpense = DB::table('staff_ledgers')
        ->whereBetween('created_at', [$from, $to])
        ->sum('paid');

    $otherExpense = DB::table('add_expenses')
        ->whereBetween('date', [$from, $to])
        ->sum('amount');

    $overallExpense = $staffExpense + $otherExpense;

    return response()->json([
        'jobs'           => $jobRows,
        'totalJobs'      => count($jobRows),
        'totalAmount'    => number_format($totalJobAmount, 2),
        'overallExpense' => number_format($overallExpense, 2),
        'netProfit'      => number_format($totalJobProfit - $overallExpense, 2),
    ]);
}
}