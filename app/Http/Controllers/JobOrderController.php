<?php

namespace App\Http\Controllers;

use App\Models\Contractor;
use App\Models\JobOrder;
use App\Models\LocalSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JobOrderController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $jobOrders = JobOrder::where('admin_or_user_id', $userId)
            ->latest()
            ->get();

        $localSales = LocalSale::with('customer')
            ->select('id', 'invoice_number', 'customer_id')
            ->latest()
            ->get();

        $contractors = Contractor::where('admin_or_user_id', $userId)->get();

        return view(
            'admin_panel.salesmen.add_joborder',
            compact('jobOrders', 'localSales', 'contractors')
        );
    }

    public function getSaleDetails($saleId)
    {
        $sale = LocalSale::where('id', $saleId)->first();

        if (! $sale) {
            return response()->json([
                'status' => false,
                'message' => 'Sale not found',
            ], 404);
        }

        $items = json_decode($sale->item, true) ?? [];
        $qtys = json_decode($sale->qty, true) ?? [];
        $units = json_decode($sale->unit, true) ?? [];
        $amounts = json_decode($sale->amount, true) ?? [];

        $formattedItems = [];

        foreach ($items as $index => $name) {
            $formattedItems[] = [
                'id' => $sale->id,
                'item' => $name,
                'qty' => $qtys[$index] ?? 1,
                'unit' => $units[$index] ?? null,
                'rate' => $amounts[$index] ?? 0,
            ];
        }

        return response()->json([
            'status' => true,
            'items' => $formattedItems,
        ]);
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {

            if (! isset($request->work_types) || count($request->work_types) === 0) {
                return response()->json(['error' => 'Work types required'], 422);
            }

            // ✅ Get first work type for default staff
            $firstWork = $request->work_types[0];

            // ✅ Generate Job Order Number
            $userId = Auth::id();
            $lastJob = JobOrder::where('admin_or_user_id', $userId)
                ->orderBy('id', 'desc')
                ->first();

            if ($lastJob && $lastJob->job_order_no) {
                // Extract number from format like "JOB-0001"
                $lastNumber = (int) substr($lastJob->job_order_no, 4);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            $jobOrderNo = 'JOB-'.str_pad($newNumber, 4, '0', STR_PAD_LEFT);

            JobOrder::create([
                'admin_or_user_id' => $userId,
                'job_order_no' => $jobOrderNo, // ✅ ADDED
                'staff_id' => $firstWork['contractor'] ?? null,
                'staff_type' => $firstWork['assign_type'] ?? null,

                // ✅ Store all work types as JSON
                'work_type' => json_encode($request->work_types),

                'job_date' => $request->job_date,
                'total_amount' => $request->total_amount,
                'paid_amount' => $request->paid_amount ?? 0,
                'remaining_amount' => $request->total_amount - ($request->paid_amount ?? 0),
                'status' => 'pending',
            ]);
        });

        return response()->json(['status' => true]);
    }

    public function show($id)
    {
        $job = JobOrder::where('id', $id)
            ->where('admin_or_user_id', Auth::id())
            ->firstOrFail();

        $jobItems = DB::table('job_items')
            ->where('job_order_id', $job->id)
            ->orderBy('id')
            ->get()
            ->groupBy('work_type');

        return view(
            'admin_panel.salesmen.joborder_detail',
            compact('job', 'jobItems')
        );
    }

    public function update(Request $request)
    {
        $job = JobOrder::find($request->job_id);

        if (! $job) {
            return back()->with('error', 'Job Order not found');
        }

        $job->update([
            'job_date' => $request->job_date,
            'work_type' => json_encode($request->work_types), // ✅ FIX
            'total_amount' => $request->total_amount,
            'paid_amount' => $request->paid_amount,
            'remaining_amount' => $request->total_amount - $request->paid_amount,
            'status' => ($request->total_amount - $request->paid_amount) > 0 ? 'pending' : 'completed',
        ]);

        return back()->with('success', 'Job Order updated successfully');
    }

    public function delete($id)
    {
        $job = JobOrder::find($id);

        if (! $job) {
            return response()->json(['status' => false]);
        }

        DB::table('job_items')->where('job_order_id', $id)->delete();
        $job->delete();

        return response()->json(['status' => true]);
    }

    public function toggleStatus(Request $request)
    {
        $job = JobOrder::find($request->job_id);

        if (! $job) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $job->status = $request->status;
        $job->save();

        return response()->json(['success' => true]);
    }
}
