<?php

namespace App\Http\Controllers;

use App\Models\CustomerLedger;
use App\Models\LocalSale;
use App\Models\Sale;
use App\Models\SaleReturn;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SaleReturnController extends Controller
{
    public function add_sale_return()
    {
        if (Auth::id()) {
            // show the blade you created earlier (add_sale_return)
            return view('admin_panel.sale_return.add_sale_return');
        }

        return redirect()->back();
    }

    public function getSaleInvoices(Request $request)
    {
        $user = Auth::user();
        $date = $request->get('date'); // optional yyyy-mm-dd
        $search = trim($request->get('search', ''));

        // Always use local_sales for local customer invoices
        $query = DB::table('local_sales')->where('admin_or_user_id', $user->id);

        if ($date) {
            $query->whereDate('Date', $date);
        }

        if ($search !== '') {
            $query->where('invoice_number', 'like', '%'.$search.'%');
        }

        $rows = $query->orderBy('id', 'desc')
            ->get(['id', 'invoice_number', 'item', 'customer_id']);

        $result = collect($rows)->map(function ($row) {
            return [
                'invoice_number' => $row->invoice_number,
                'party_id' => $row->customer_id ?? null,
                'first_item' => null,
                'label' => $row->invoice_number, // simple label
            ];
        })->unique('invoice_number')->values();

        return response()->json($result);
    }

    public function fetchSaleDetails(Request $request)
    {
        $type = $request->input('sale_type');
        $invoiceNumber = $request->input('invoice_number');

        if (! $type || ! $invoiceNumber) {
            return response()->json([
                'success' => false,
                'message' => 'Sale type and invoice number are required.',
            ]);
        }

        if ($type === 'distributor') {
            $sale = Sale::with('distributor')->where('invoice_number', $invoiceNumber)->first();
        } elseif ($type === 'customer') {
            $sale = LocalSale::with('customer')->where('invoice_number', $invoiceNumber)->first();
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid sale type.']);
        }

        if (! $sale) {
            return response()->json(['success' => false, 'message' => 'Sale details not found.']);
        }

        try {
            $createdAt = Carbon::parse($sale->created_at);
        } catch (\Exception $e) {
            $createdAt = null;
        }

        $items = json_decode($sale->item, true) ?? [];
        $cartonQty = json_decode($sale->carton_qty, true) ?? [];
        $pcsQty = json_decode($sale->pcs, true) ?? [];
        $liter = json_decode($sale->liter, true) ?? [];
        $rate = json_decode($sale->rate, true) ?? [];
        $discount = json_decode($sale->discount, true) ?? [];
        $returnQty = json_decode($sale->return_qty, true) ?? [];
        $pcscarton = json_decode($sale->pcs_carton, true) ?? [];

        $partyName = $type === 'distributor'
            ? ($sale->distributor->Customer ?? 'N/A')
            : ($sale->customer->customer_name ?? 'N/A');

        $rows = [];
        $grandTotal = 0;
        $totalReturnAmount = 0;
        $count = count($items);

        for ($index = 0; $index < $count; $index++) {
            $cartonQuantity = (float) ($cartonQty[$index] ?? 0);
            $pcsQuantity = (float) ($pcsQty[$index] ?? 0);
            $rateAmount = (float) ($rate[$index] ?? 0);
            $pcsPerCarton = (float) ($pcscarton[$index] ?? 0);
            $returnQtyValue = (float) ($returnQty[$index] ?? 0);
            $discountAmount = (float) ($discount[$index] ?? 0);
            $literQty = (float) ($liter[$index] ?? 0);

            $cartonTotal = $cartonQuantity * $rateAmount;
            $pcsTotal = 0;
            if ($pcsPerCarton > 0 && $pcsQuantity > 0) {
                $ratePerPcs = $rateAmount / $pcsPerCarton;
                $pcsTotal = $pcsQuantity * $ratePerPcs;
            }
            $itemTotal = $cartonTotal + $pcsTotal;

            $grandTotal += $itemTotal;
            $totalReturnAmount += $returnQtyValue * $rateAmount;

            $rows[] = [
                'invoice_number' => $sale->invoice_number,
                'date' => $createdAt ? $createdAt->format('Y-m-d') : 'N/A',
                'distributor' => $partyName,
                'item' => $items[$index] ?? 'N/A',
                'carton_quantity' => $cartonQuantity,
                'pcs_quantity' => $pcsQuantity,
                'liter' => $literQty,
                'rate' => $rateAmount,
                'discount_amount' => $discountAmount,
                'packing' => $pcsPerCarton,
                'return_qty' => $returnQtyValue,
                'return_amount' => round($returnQtyValue * $rateAmount, 2),
                'item_total' => round($itemTotal, 2),
            ];
        }

        return response()->json([
            'success' => true,
            'sales' => $rows,
            'summary' => [
                'grand_total' => round($grandTotal, 2),
                'discount_value' => $sale->discount_value ?? 0,
                'scheme_value' => $sale->scheme_value ?? 0,
                'net_amount' => $sale->net_amount ?? 0,
                'total_return_amount' => round($totalReturnAmount, 2),
            ],
            'party_id' => $type === 'distributor' ? $sale->distributor_id : $sale->customer_id,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'sale_type' => 'required|string|in:customer,distributor',
            'party_id' => 'required',
            'invoice_number' => 'required|string',
            'return_items' => 'required|array|min:1',
            'return_items.*.item_id' => 'nullable',
            'return_items.*.item_name' => 'required|string',
            'return_items.*.pcs_per_carton' => 'nullable|integer',
            'return_items.*.carton_qty' => 'nullable|integer|min:0',
            'return_items.*.pcs_qty' => 'nullable|integer|min:0',
            'return_items.*.rate' => 'required|numeric|min:0',
            'return_items.*.discount' => 'nullable|numeric|min:0',
            'return_items.*.total' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user = Auth::user();
        $userId = $user->id;

        $items = collect($data['return_items']);
        $totalReturnAmount = $items->sum('total');

        DB::beginTransaction();
        try {
            $saleReturn = SaleReturn::create([
                'admin_or_user_id' => $userId,
                'sale_type' => $data['sale_type'],
                'party_id' => $data['party_id'],
                'invoice_number' => $data['invoice_number'],
                'item_ids' => $items->pluck('item_id')->filter()->implode(','),
                'item_names' => $items->pluck('item_name')->implode(','),
                'pcs_per_carton' => $items->pluck('pcs_per_carton')->implode(','),
                'carton_qty' => $items->pluck('carton_qty')->implode(','),
                'pcs_qty' => $items->pluck('pcs_qty')->implode(','),
                'rate' => $items->pluck('rate')->implode(','),
                'discount' => $items->pluck('discount')->implode(','),
                'total' => $items->pluck('total')->implode(','),
                'total_return_amount' => $totalReturnAmount,
            ]);

            if ($data['sale_type'] === 'distributor') {
                $sale = Sale::where('invoice_number', $data['invoice_number'])->first();
                if ($sale) {
                    $sale->return_status = 1;
                    $sale->save();
                }

                $ledger = DB::table('distributor_ledgers')->where('distributor_id', $data['party_id'])->first();
                if ($ledger) {
                    $newClosingBalance = ($ledger->closing_balance ?? 0) - $totalReturnAmount;
                    DB::table('distributor_ledgers')
                        ->where('distributor_id', $data['party_id'])
                        ->update(['closing_balance' => $newClosingBalance]);
                }
            } else {
                $localSale = LocalSale::where('invoice_number', $data['invoice_number'])->first();
                if ($localSale) {
                    $localSale->return_status = 1;
                    $localSale->save();
                }

                CustomerLedger::where('customer_id', $data['party_id'])
                    ->decrement('closing_balance', $totalReturnAmount);
            }

            foreach ($items as $item) {
                $cartons = intval($item['carton_qty'] ?? 0);
                $pcs = intval($item['pcs_qty'] ?? 0);
                $itemName = $item['item_name'] ?? null;
                $itemId = $item['item_id'] ?? null;

                if ($user->usertype === 'distributor') {
                    $distId = $userId;
                    $q = DB::table('distributor_products')->where('distributor_id', $distId);
                    if ($itemId) {
                        $q->where('item_id', $itemId);
                    } elseif ($itemName) {
                        $q->where('item', $itemName);
                    }
                    if ($cartons > 0) {
                        $q->increment('carton_quantity', $cartons);
                    }
                    if ($pcs > 0) {
                        $q->increment('loose_pieces', $pcs);
                    }
                } else {
                    $q = DB::table('products')->where('admin_or_user_id', $userId);
                    if ($itemId) {
                        $q->where('id', $itemId);
                    } elseif ($itemName) {
                        $q->where('item_name', $itemName);
                    }
                    if ($cartons > 0) {
                        $q->increment('carton_quantity', $cartons);
                    }
                    if ($pcs > 0) {
                        $q->increment('loose_pieces', $pcs);
                    }
                }
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Sale return recorded and stock updated successfully.', 'data' => $saleReturn]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('SaleReturn store error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to save return. '.$e->getMessage()], 500);
        }
    }

    public function all_sale_return()
    {
        if (! Auth::id()) {
            return redirect()->back();
        }

        $userId = Auth::id();
        $salesReturns = SaleReturn::with(['distributor', 'customer'])
            ->where('admin_or_user_id', $userId)
            ->orderBy('id', 'desc')
            ->get();

        return view('admin_panel.sale_return.all_sale_return', compact('salesReturns'));
    }
}
