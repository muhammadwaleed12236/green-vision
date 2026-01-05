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

    /* ================= JOB ORDERS ================= */
    $jobOrders = DB::table('job_orders')
        ->whereBetween('job_date', [$from, $to])
        ->get();

    $jobRows = [];
    $totalJobAmount = 0;
    $totalJobProfit = 0;

    foreach ($jobOrders as $job) {

        /* ===== STOCK COST (JOB BASED) ===== */
        $stockCost = DB::table('stock_outs')
            ->where('local_sales_id', $job->id)
            ->sum('total_stock');

        $jobAmount = $job->total_amount ?? 0;

        // ✅ CORRECT JOB PROFIT
        $jobProfit = $jobAmount - $stockCost;

        $jobRows[] = [
            'job'        => $job->job_order_no,
            'staff'      => 'Staff ID: ' . $job->staff_id,
            'job_amount' => $jobAmount,
            'staff_cost' => 0, // ❌ job-wise nahi
            'stock_cost' => $stockCost,
            'profit'     => $jobProfit,
        ];

        $totalJobAmount += $jobAmount;
        $totalJobProfit += $jobProfit;
    }

    /* ================= STAFF LEDGER (OVERALL) ================= */
$staffExpense = DB::table('staff_ledgers')
    ->whereBetween('ledger_date', [$from, $to])
    ->sum('paid');

    /* ================= OTHER EXPENSE ================= */
    $otherExpense = DB::table('add_expenses')
        ->whereBetween('date', [$from, $to])
        ->sum('amount');

    $overallExpense = $staffExpense + $otherExpense;

    return response()->json([
        'jobs'           => $jobRows,
        'totalJobs'      => count($jobRows),
        'totalAmount'    => $totalJobAmount,
        'overallExpense' => $overallExpense,
        'netProfit'      => $totalJobProfit - $overallExpense,
    ]);
}

}
