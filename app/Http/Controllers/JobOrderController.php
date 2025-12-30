<?php

namespace App\Http\Controllers;

use App\Models\JobOrder;
use App\Models\LocalSale;
use App\Models\StockOut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JobOrderController extends Controller
{
    /* ================= INDEX ================= */
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

        return view(
            'admin_panel.salesmen.add_joborder',
            compact('jobOrders', 'localSales')
        );
    }

    /* =====================================================
       FETCH SALE ITEMS (FOR WORK TYPE ASSIGNMENT)
       ===================================================== */
    public function getSaleDetails($saleId)
    {
        $items = StockOut::where('local_sale_id', $saleId)
            ->select('id', 'item', 'qty')
            ->get();

        return response()->json([
            'status' => true,
            'items' => $items,
        ]);
    }

    /* ================= STORE JOB ================= */
    public function store(Request $request)
    {
        $request->validate([
            'job_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'work_types' => 'required|array',
        ]);

        DB::transaction(function () use ($request) {

            /* ---------- JOB HEADER ---------- */
            $job = JobOrder::create([
                'admin_or_user_id' => Auth::id(),
                'job_order_no' => 'JOB-'.str_pad(JobOrder::max('id') + 1, 4, '0', STR_PAD_LEFT),
                'job_date' => $request->job_date,
                'total_amount' => $request->total_amount,
                'paid_amount' => $request->paid_amount ?? 0,
                'remaining_amount' => $request->total_amount - ($request->paid_amount ?? 0),
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /* ---------- WORK TYPES + ITEMS ---------- */
            foreach ($request->work_types as $workType) {

                foreach ($workType['items'] as $item) {

                    DB::table('job_items')->insert([
                        'job_order_id' => $job->id,
                        'work_type' => $workType['name'],              // Glass / Aluminium
                        'assign_type' => $workType['assign_type'],       // labour / contract
                        'contractor' => $workType['contractor'] ?? null,
                        'item_id' => $item['id'],
                        'qty' => $item['qty'],
                        'rate' => $item['rate'],
                        'total' => $item['qty'] * $item['rate'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        return redirect()
            ->route('job-orders.index')
            ->with('success', 'Job Order created successfully');
    }

    /* ================= UPDATE ================= */
    public function update(Request $request)
    {
        $job = JobOrder::find($request->job_id);

        if (! $job) {
            return redirect()->back()->with('error', 'Job Order not found');
        }

        $paid = $request->paid_amount ?? 0;

        $job->update([
            'job_date' => $request->job_date,
            'total_amount' => $request->total_amount,
            'paid_amount' => $paid,
            'remaining_amount' => $request->total_amount - $paid,
            'status' => ($request->total_amount - $paid) > 0 ? 'pending' : 'completed',
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Job Order updated successfully');
    }

    /* ================= DELETE ================= */
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

    /* ================= STATUS TOGGLE ================= */
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
