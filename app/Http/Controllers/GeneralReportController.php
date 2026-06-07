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

        // 1. Fetch Job Orders
        $jobOrders = \App\Models\JobOrder::with(['contractor', 'vendor', 'salesman'])
            ->whereBetween('order_date', [$from, $to])
            ->get();

        $jobRows = [];
        $totalJobAmount = 0;

        foreach ($jobOrders as $job) {
            $staffName = 'N/A';
            if ($job->assignee_type === 'contractor' && $job->contractor) {
                $staffName = $job->contractor->contractor_name;
            } elseif ($job->assignee_type === 'vendor' && $job->vendor) {
                $staffName = $job->vendor->Party_name;
            } elseif ($job->assignee_type === 'inhouse' && $job->salesman) {
                $staffName = $job->salesman->salesman_name;
            } elseif ($job->salesman) { // Fallback for old data
                 $staffName = $job->salesman->salesman_name;
            }

            $jobAmount = $job->total_amount;
            $totalJobAmount += $jobAmount;

            $jobRows[] = [
                'job'          => $job->job_order_number,
                'date'         => \Carbon\Carbon::parse($job->order_date)->format('d-m-Y'),
                'staff'        => $staffName,
                'job_amount'   => number_format($jobAmount, 2),
            ];
        }

        // 2. Expenses

        // A. Stock Outs Cost
        $stockCost = DB::table('stock_outs')
            ->whereBetween('stock_out_date', [$from, $to]) // Use stock_out_date if populated, otherwise created_at
             // Fallback if stock_out_date is null in some records? Migration says nullable.
             // Ideally we trust stock_out_date.
            ->sum('total_stock');

        // B. Staff Salaries
        $salaryExpense = DB::table('staff_salary_payments')
            ->whereBetween('payment_date', [$from, $to])
            ->sum('amount_paid');

        // C. Other Expenses
        $otherExpense = DB::table('add_expenses')
            ->whereBetween('expense_date', [$from, $to])
            ->sum('amount');

        $overallExpense = $stockCost + $salaryExpense + $otherExpense;
        $netProfit = $totalJobAmount - $overallExpense;

        return response()->json([
            'jobs'           => $jobRows,
            'totalJobs'      => count($jobRows),
            'totalAmount'    => number_format($totalJobAmount, 2),
            'overallExpense' => number_format($overallExpense, 2),
            'netProfit'      => number_format($netProfit, 2),
        ]);
    }
}