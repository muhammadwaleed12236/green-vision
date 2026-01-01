<?php

namespace App\Http\Controllers;

use App\Models\Contractor;
use App\Models\ContractorLedger;
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
        $request->validate([
            'sale_id' => 'required',
            'job_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'work_types' => 'required|array',
        ]);

        try {

            DB::transaction(function () use ($request) {

                /* ================= JOB HEADER ================= */
                $job = JobOrder::create([
                    'admin_or_user_id' => Auth::id(),
                    'staff_id' => Auth::id(),
                    'job_order_no' => 'JOB-'.str_pad((JobOrder::max('id') ?? 0) + 1, 4, '0', STR_PAD_LEFT),
                    'job_date' => $request->job_date,
                    'total_amount' => $request->total_amount,
                    'paid_amount' => $request->paid_amount ?? 0,
                    'remaining_amount' => $request->total_amount - ($request->paid_amount ?? 0),
                    'status' => ($request->total_amount - ($request->paid_amount ?? 0)) > 0 ? 'pending' : 'completed',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                /* ================= JOB ITEMS ================= */
                foreach ($request->work_types as $workType) {

                    foreach ($workType['items'] as $item) {

                        DB::table('job_items')->insert([
                            'job_order_id' => $job->id,
                            'work_type' => $workType['name'],
                            'assign_type' => $workType['assign_type'],
                            'contractor' => $workType['contractor'] ?? null,
                            'item_id' => $item['id'] ?? null,
                            'item_name' => $item['name'],
                            'qty' => $item['qty'],
                            'rate' => $item['rate'],
                            'total' => $item['qty'] * $item['rate'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                /* ================= CONTRACTOR LEDGER ================= */
                foreach ($request->work_types as $workType) {

                    // only contractor case
                    if ($workType['assign_type'] !== 'contract' || empty($workType['contractor'])) {
                        continue;
                    }

                    $contractorId = $workType['contractor'];

                    // contractor ka total kaam
                    $contractorJobTotal = 0;
                    foreach ($workType['items'] as $item) {
                        $contractorJobTotal += ($item['qty'] * $item['rate']);
                    }

                    $paid = $request->paid_amount ?? 0;

                    $ledger = ContractorLedger::where('contractor_id', $contractorId)->first();

                    if ($ledger) {

                        $previous = $ledger->closing_balance;
                        $closing = $previous + $contractorJobTotal - $paid;

                        $ledger->update([
                            'previous_balance' => $previous,
                            'closing_balance' => $closing,
                            'updated_at' => now(),
                        ]);

                    } else {

                        // first time contractor
                        ContractorLedger::create([
                            'contractor_id' => $contractorId,
                            'opening_balance' => 0,
                            'previous_balance' => 0,
                            'closing_balance' => $contractorJobTotal - $paid,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            });

            return redirect()
                ->route('job-orders.index')
                ->with('success', 'Job Order created & contractor ledger updated successfully');

        } catch (\Exception $e) {

            return back()->with('error', $e->getMessage());
        }
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
