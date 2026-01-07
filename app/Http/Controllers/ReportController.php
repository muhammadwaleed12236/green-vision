<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Models\Contractor;
use App\Models\Customer;
use App\Models\CustomerRecovery;
use App\Models\Distributor;
use App\Models\JobOrder;
use App\Models\LocalSale;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Recovery;
use App\Models\Sale;
use App\Models\Salesman;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function Distributor_Ledger_Record()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Distributors = Distributor::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure

            return view('admin_panel.reports.distributor_ledger_record', [
                'Distributors' => $Distributors,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function fetchDistributorLedger(Request $request)
    {
        $distributorId = $request->input('distributor_id');

        $startDate = $request->input('start_date').' 00:00:00';
        $endDate = $request->input('end_date').' 23:59:59';

        $ledger = DB::table('distributor_ledgers')
            ->where('distributor_id', $distributorId)
            ->select('opening_balance')
            ->first();

        $baseOpening = $ledger->opening_balance ?? 0;

        // ---- Transactions Before Start Date ----
        $previousSales = DB::table('sales')
            ->where('distributor_id', $distributorId)
            ->where('Date', '<', $startDate)
            ->sum('net_amount');

        $previousRecoveries = DB::table('recoveries')
            ->where('distributor_ledger_id', $distributorId)
            ->where('date', '<', $startDate)
            ->sum('amount_paid');

        $previousReturns = DB::table('sale_returns')
            ->where('sale_type', 'distributor')
            ->where('party_id', $distributorId)
            ->where('created_at', '<', $startDate)
            ->sum('total_return_amount');

        // ✅ Opening Balance = BaseOpening + (Sales − Recoveries − Returns)
        $openingBalance = $baseOpening + $previousSales - ($previousRecoveries + $previousReturns);

        // ---- Current Period Transactions ----
        $recoveries = DB::table('recoveries')
            ->where('distributor_ledger_id', $distributorId)
            ->whereBetween('date', [$startDate, $endDate])
            ->select('id', 'amount_paid', 'salesman', 'date', 'remarks')
            ->get();

        $sales = DB::table('sales')
            ->where('distributor_id', $distributorId)
            ->whereBetween('Date', [$startDate, $endDate])
            ->select('invoice_number', 'Date', 'Booker', 'Saleman', 'grand_total', 'discount_value', 'scheme_value', 'net_amount')
            ->get();

        $saleReturns = DB::table('sale_returns')
            ->where('sale_type', 'distributor')
            ->where('party_id', $distributorId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('invoice_number', 'created_at', 'total_return_amount')
            ->get();

        // ✅ Closing Balance = Opening + Sales − (Recoveries + Returns)
        $closingBalance = $openingBalance
            + $sales->sum('net_amount')
            - ($recoveries->sum('amount_paid') + $saleReturns->sum('total_return_amount'));

        // ---- Distributor Balance Transfers (Jo is distributor ko mile hain)
        $transfers = DB::table('distributor_balance_transfers')
            ->where('to_distributor', $distributorId)
            ->whereBetween('transfer_date', [$startDate, $endDate])
            ->select('id', 'from_distributor', 'to_distributor', 'amount', 'transfer_date', 'reason')
            ->get();

        return response()->json([
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'recoveries' => $recoveries,
            'sales' => $sales,
            'sale_returns' => $saleReturns,
            'transfers' => $transfers,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function vendor_Ledger_Record()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Vendors = Vendor::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure

            return view('admin_panel.reports.vendor_ledger_record', [
                'Vendors' => $Vendors,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function fetchVendorLedger(Request $request)
    {
        $vendorId = $request->input('Vendor_id');
        $startDate = $request->input('start_date').' 00:00:00';
        $endDate = $request->input('end_date').' 23:59:59';

        // ---- Get Base Opening from Vendor Ledger ----
        $ledger = DB::table('vendor_ledgers')
            ->where('vendor_id', $vendorId)
            ->select('opening_balance')
            ->first();

        $baseOpening = $ledger->opening_balance ?? 0;

        // ---- Transactions Before Start Date ----
        $previousPurchases = DB::table('purchases')
            ->where('party_name', $vendorId)
            ->where('purchase_date', '<', $startDate)
            ->sum('grand_total');

        $previousPayments = DB::table('vendor_payments')
            ->where('vendor_id', $vendorId)
            ->where('payment_date', '<', $startDate)
            ->sum('amount_paid');

        $previousReturnsRaw = DB::table('purchase_returns')
            ->where('party_name', $vendorId)
            ->where('return_date', '<', $startDate)
            ->get();

        $previousReturns = 0;
        foreach ($previousReturnsRaw as $return) {
            $amountArray = json_decode($return->return_amount, true);
            $previousReturns += collect($amountArray)->sum();
        }

        $previousBuilties = DB::table('vendor_builties')
            ->where('vendor_id', $vendorId)
            ->where('date', '<', $startDate)
            ->sum('amount');

        // ✅ Opening Balance = BaseOpening + Purchases + Builties − (Payments + Returns)
        $openingBalance = $baseOpening + ($previousPurchases + $previousBuilties) - ($previousPayments + $previousReturns);

        // ---- Current Period Transactions ----
        $recoveries = DB::table('vendor_payments')
            ->where('vendor_id', $vendorId)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->select('id', 'amount_paid', 'description', 'payment_date')
            ->get();

        $purchases = DB::table('purchases')
            ->where('party_name', $vendorId)
            ->whereBetween('purchase_date', [$startDate, $endDate])
            ->select('id', 'invoice_number', 'purchase_date', 'grand_total')
            ->get()
            ->map(function ($purchase) {
                return [
                    'invoice_number' => $purchase->invoice_number,
                    'date' => $purchase->purchase_date,
                    'grand_total' => $purchase->grand_total,
                    'net_amount' => $purchase->grand_total,
                ];
            });

        $returnsRaw = DB::table('purchase_returns')
            ->where('party_name', $vendorId)
            ->whereBetween('return_date', [$startDate, $endDate])
            ->get();

        $returns = [];
        $currentReturns = 0;
        foreach ($returnsRaw as $return) {
            $amountArray = json_decode($return->return_amount, true);
            $amountSum = collect($amountArray)->sum();
            $currentReturns += $amountSum;

            $returns[] = [
                'id' => $return->id,
                'invoice_number' => $return->invoice_number,
                'date' => $return->return_date,
                'net_amount' => $amountSum,
            ];
        }

        $builties = DB::table('vendor_builties')
            ->where('vendor_id', $vendorId)
            ->whereBetween('date', [$startDate, $endDate])
            ->select('id', 'date', 'amount', 'description')
            ->get();

        // ✅ Closing Balance = Opening + Purchases + Builties − (Payments + Returns)
        $closingBalance = $openingBalance
            + $purchases->sum('grand_total')
            + $builties->sum('amount')
            - ($recoveries->sum('amount_paid') + $currentReturns);

        return response()->json([
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'purchases' => $purchases,
            'recoveries' => $recoveries,
            'returns' => $returns,
            'builties' => $builties,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function Customer_Ledger_Record()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Customers = Customer::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure

            return view('admin_panel.reports.customer_ledger_record', [
                'Customers' => $Customers,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function fetchCustomerLedger(Request $request)
    {
        $CustomerId = $request->input('Customer_id');
        if (! $CustomerId) {
            return response()->json(['message' => 'Customer_id is required'], 422);
        }

        $startDate = ($request->input('start_date') ?: date('Y-m-d')).' 00:00:00';
        $endDate = ($request->input('end_date') ?: now()->format('Y-m-d')).' 23:59:59';

        /* ---- Base opening from ledger (NO carry-forward) ---- */
        $ledgerRow = DB::table('customer_ledgers')
            ->where('customer_id', $CustomerId)
            ->latest('id')
            ->select('id', 'opening_balance', 'closing_balance', 'created_at')
            ->first();

        $openingBalance = (float) ($ledgerRow->opening_balance ?? 0); // ← fixed opening
        $carryFwd = (float) ($ledgerRow->closing_balance ?? 0); // info only

        /* ---- Period details (for listing) ---- */
        $recoveries = DB::table('customer_recoveries as cr')
            ->join('customer_ledgers as cl', 'cl.id', '=', 'cr.customer_ledger_id')
            ->where('cl.customer_id', $CustomerId)
            ->whereBetween('cr.date', [$startDate, $endDate])
            ->select('cr.id', 'cr.amount_paid', 'cr.salesman', 'cr.date', 'cr.remarks')
            ->get();

        $localSales = DB::table('local_sales')
            ->where('customer_id', $CustomerId)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('Date', [$startDate, $endDate])
                    ->orWhere(function ($qq) use ($startDate, $endDate) {
                        $qq->whereNull('Date')->whereBetween('created_at', [$startDate, $endDate]);
                    });
            })
            ->select('invoice_number', 'Date', 'customer_shopname', 'grand_total',
                'discount_value', 'scheme_value', 'net_amount', 'created_at')
            ->get();

        $saleReturns = DB::table('sale_returns')
            ->where('sale_type', 'customer')
            ->where('party_id', $CustomerId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('invoice_number', 'total_return_amount', 'created_at')
            ->get();

        /* ---- Upto endDate aggregates (for closing) ---- */
        $salesUptoEnd = (float) DB::table('local_sales')
            ->where('customer_id', $CustomerId)
            ->where(function ($q) use ($endDate) {
                $q->where('Date', '<=', $endDate)
                    ->orWhere(function ($qq) use ($endDate) {
                        $qq->whereNull('Date')->where('created_at', '<=', $endDate);
                    });
            })
            ->sum('net_amount');

        $cashAtSaleUptoEnd = (float) DB::table('local_sales')
            ->where('customer_id', $CustomerId)
            ->where(function ($q) use ($endDate) {
                $q->where('Date', '<=', $endDate)
                    ->orWhere(function ($qq) use ($endDate) {
                        $qq->whereNull('Date')->where('created_at', '<=', $endDate);
                    });
            })
            ->sum('grand_total');

        $recoveriesUptoEnd = (float) DB::table('customer_recoveries as cr')
            ->join('customer_ledgers as cl', 'cl.id', '=', 'cr.customer_ledger_id')
            ->where('cl.customer_id', $CustomerId)
            ->where('cr.date', '<=', $endDate)
            ->sum('cr.amount_paid');

        $returnsUptoEnd = (float) DB::table('sale_returns')
            ->where('sale_type', 'customer')
            ->where('party_id', $CustomerId)
            ->where('created_at', '<=', $endDate)
            ->sum('total_return_amount');

        // Assumption: sale-time cash_received NOT in recoveries table → include both
        $closingBalance = $openingBalance
            + $salesUptoEnd
            - ($recoveriesUptoEnd + $cashAtSaleUptoEnd + $returnsUptoEnd);

        return response()->json([
            // snapshot
            'opening_balance' => round($openingBalance, 2),
            'closing_balance' => round($closingBalance, 2),

            // preferred names
            'opening_as_of_start' => round($openingBalance, 2),
            'closing_as_of_end' => round($closingBalance, 2),

            // info
            'ledger_closing_balance' => round($carryFwd, 2),

            // period data (lists)
            'recoveries' => $recoveries,
            'local_sales' => $localSales,
            'sale_returns' => $saleReturns,

            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function stock_Record()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $categories = Category::where('admin_or_user_id', $userId)->get();

            return view('admin_panel.reports.stock_Record', [
                'categories' => $categories,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function getItems($subcategory)
    {
        $items = Product::where('sub_category', $subcategory)->select('item_code', 'item_name')->get();

        return response()->json($items);
    }



    public function getItemDetails(Request $request)
{
    \Log::info('Filter Request:', [
        'category' => $request->category,
        'subcategory' => $request->subcategory,
        'itemCode' => $request->itemCode,
    ]);

    $query = Product::query();

    if ($request->category !== 'all') {
        $query->where('category', 'LIKE', '%'.$request->category.'%');
    }

    if ($request->subcategory !== 'all') {
        $query->where('sub_category', 'LIKE', '%'.$request->subcategory.'%');
    }

    if ($request->itemCode !== 'all') {
        $query->where('item_code', $request->itemCode);
    }

    $items = $query->get();

    if ($items->isEmpty()) {
        \Log::info('No items found with filters:', [
            'category' => $request->category,
            'subcategory' => $request->subcategory,
            'itemCode' => $request->itemCode,
        ]);
    }

    foreach ($items as $item) {

        $pcsInCarton = max((int) $item->pcs_in_carton, 1);

        $openingTotalPCS = (int) ($item->initial_stock ?? 0);

        $item->opening_carton = intdiv($openingTotalPCS, $pcsInCarton);
        $item->opening_pcs = $openingTotalPCS % $pcsInCarton;

        $purchasePCS = 0;

        foreach (
            DB::table('purchases')
                ->whereJsonContains('item', $item->item_name)
                ->get() as $purchase
        ) {
            $names = json_decode($purchase->item, true) ?? [];
            $cartons = json_decode($purchase->carton_qty, true) ?? [];
            $pcs = json_decode($purchase->pcs ?? '[]', true);

            foreach ($names as $i => $n) {
                if (trim($n) === trim($item->item_name)) {
                    $purchasePCS +=
                        ((int) ($cartons[$i] ?? 0) * $pcsInCarton)
                        + (int) ($pcs[$i] ?? 0);
                }
            }
        }

        $item->purchase_carton = intdiv($purchasePCS, $pcsInCarton);
        $item->purchase_pcs = $purchasePCS % $pcsInCarton;

        $purchaseReturnPCS = 0;

        foreach (
            DB::table('purchase_returns')
                ->whereJsonContains('item', $item->item_name)
                ->get() as $pr
        ) {
            $names = json_decode($pr->item, true) ?? [];
            $cartons = json_decode($pr->carton_qty ?? '[]', true) ?? [];
            $pcs = json_decode($pr->return_qty ?? '[]', true) ?? [];

            foreach ($names as $i => $n) {
                if (trim($n) === trim($item->item_name)) {
                    $purchaseReturnPCS +=
                        ((int) ($cartons[$i] ?? 0) * $pcsInCarton)
                        + (int) ($pcs[$i] ?? 0);
                }
            }
        }

        $item->purchase_return_carton = intdiv($purchaseReturnPCS, $pcsInCarton);
        $item->purchase_return_pcs = $purchaseReturnPCS % $pcsInCarton;

        /* ================= 🔥 STOCK OUT (JOB ORDERS) ================= */
        $stockOutPCS = 0;

// Stock outs table se product_id se match karke total_stock sum karein
$stockOutRecords = DB::table('stock_outs')
    ->where('product_id', $item->id)
    ->get();

foreach ($stockOutRecords as $stockOut) {
    // total_stock = jo stock use hua hai (current_stock - close_stock)
    $usedStock = (float)$stockOut->current_stock - (float)$stockOut->close_stock;
    $stockOutPCS += abs($usedStock); // absolute value lein
}

$item->stock_out_carton = intdiv($stockOutPCS, $pcsInCarton);
$item->stock_out_pcs = $stockOutPCS % $pcsInCarton;

        /* ================= LOCAL SOLD (DISPLAY ONLY) ================= */
        $soldPCS = 0;

        foreach (LocalSale::whereJsonContains('item', $item->item_name)->get() as $sale) {
            $names = json_decode($sale->item, true) ?? [];
            $cartons = json_decode($sale->carton_qty, true) ?? [];
            $pcs = json_decode($sale->pcs, true) ?? [];

            foreach ($names as $i => $n) {
                if (trim($n) === trim($item->item_name)) {
                    $soldPCS += (int) ($pcs[$i] ?? 0);
                }
            }
        }

        /* ================= LOCAL RETURN (DISPLAY ONLY) ================= */
        $returnPCS = 0;

        foreach (
            DB::table('sale_returns')
                ->where('sale_type', 'customer')
                ->where('item_names', $item->item_name)
                ->get() as $r
        ) {
            $pcsVal = (int) ($r->pcs_qty ?? 0);
            $cartonVal = (int) ($r->carton_qty ?? 0);

            if ($pcsVal > 0) {
                $returnPCS += $pcsVal;
            } else {
                $returnPCS += ($cartonVal * $pcsInCarton);
            }
        }

        /* ================= FINAL STOCK ================= */
        $item->balance_stock = (int) ($item->initial_stock ?? 0);
        $item->balance_wholesale_price = (float) ($item->wholesale_price ?? 0);

        /* ================= STOCK VALUE (PCS BASED) ================= */
        $wholesalePrice = (float) ($item->wholesale_price ?? 0);
        $perPcsPrice = $wholesalePrice * $item->balance_stock;
        $item->stock_value = round($perPcsPrice);

        /* ================= DISPLAY HELPERS ================= */
        $item->total_local_sold_carton = intdiv($soldPCS, $pcsInCarton);
        $item->total_local_sold_pcs = $soldPCS % $pcsInCarton;

        $item->total_local_return_carton = intdiv($returnPCS, $pcsInCarton);
        $item->total_local_return_pcs = $returnPCS % $pcsInCarton;
    }

    return response()->json($items);
}

    // public function getItemDetails(Request $request)
    // {

    //     \Log::info('Filter Request:', [
    //         'category' => $request->category,
    //         'subcategory' => $request->subcategory,
    //         'itemCode' => $request->itemCode,
    //     ]);

    //     $query = Product::query();

    //     if ($request->category !== 'all') {
    //         $query->where('category', 'LIKE', '%'.$request->category.'%');
    //     }

    //     // ✅ FIXED: Case-insensitive subcategory filtering
    //     if ($request->subcategory !== 'all') {
    //         $query->where('sub_category', 'LIKE', '%'.$request->subcategory.'%');
    //     }

    //     if ($request->itemCode !== 'all') {
    //         $query->where('item_code', $request->itemCode);
    //     }

    //     $items = $query->get();

    //     $items = $query->get();

    //     // ✅ Agar koi item nahi mila toh log mein message show karein
    //     if ($items->isEmpty()) {
    //         \Log::info('No items found with filters:', [
    //             'category' => $request->category,
    //             'subcategory' => $request->subcategory,
    //             'itemCode' => $request->itemCode,
    //         ]);
    //     }

    //     foreach ($items as $item) {

    //         /* ================= BASE ================= */
    //         $pcsInCarton = max((int) $item->pcs_in_carton, 1);

    //         /* ================= OPENING (DISPLAY ONLY) ================= */
    //         $openingTotalPCS = (int) ($item->initial_stock ?? 0);

    //         $item->opening_carton = intdiv($openingTotalPCS, $pcsInCarton);
    //         $item->opening_pcs = $openingTotalPCS % $pcsInCarton;

    //         /* ================= PURCHASE (DISPLAY ONLY) ================= */
    //         $purchasePCS = 0;

    //         foreach (
    //             DB::table('purchases')
    //                 ->whereJsonContains('item', $item->item_name)
    //                 ->get() as $purchase
    //         ) {
    //             $names = json_decode($purchase->item, true) ?? [];
    //             $cartons = json_decode($purchase->carton_qty, true) ?? [];
    //             $pcs = json_decode($purchase->pcs ?? '[]', true);

    //             foreach ($names as $i => $n) {
    //                 if (trim($n) === trim($item->item_name)) {

    //                     $purchasePCS +=
    //                         ((int) ($cartons[$i] ?? 0) * $pcsInCarton)
    //                         + (int) ($pcs[$i] ?? 0);
    //                 }
    //             }
    //         }

    //         $item->purchase_carton = intdiv($purchasePCS, $pcsInCarton);
    //         $item->purchase_pcs = $purchasePCS % $pcsInCarton;

    //         /* ================= PURCHASE RETURN (DISPLAY ONLY) ================= */
    //         $purchaseReturnPCS = 0;

    //         foreach (
    //             DB::table('purchase_returns')
    //                 ->whereJsonContains('item', $item->item_name)
    //                 ->get() as $pr
    //         ) {
    //             $names = json_decode($pr->item, true) ?? [];
    //             $cartons = json_decode($pr->carton_qty ?? '[]', true) ?? [];
    //             $pcs = json_decode($pr->return_qty ?? '[]', true) ?? [];

    //             foreach ($names as $i => $n) {
    //                 if (trim($n) === trim($item->item_name)) {

    //                     $purchaseReturnPCS +=
    //                         ((int) ($cartons[$i] ?? 0) * $pcsInCarton)
    //                         + (int) ($pcs[$i] ?? 0);
    //                 }
    //             }
    //         }

    //         $item->purchase_return_carton = intdiv($purchaseReturnPCS, $pcsInCarton);
    //         $item->purchase_return_pcs = $purchaseReturnPCS % $pcsInCarton;

    //         /* ================= LOCAL SOLD (DISPLAY ONLY) ================= */
    //         $soldPCS = 0;

    //         foreach (LocalSale::whereJsonContains('item', $item->item_name)->get() as $sale) {
    //             $names = json_decode($sale->item, true) ?? [];
    //             $cartons = json_decode($sale->carton_qty, true) ?? [];
    //             $pcs = json_decode($sale->pcs, true) ?? [];

    //             foreach ($names as $i => $n) {
    //                 if (trim($n) === trim($item->item_name)) {
    //                     $soldPCS += (int) ($pcs[$i] ?? 0);
    //                 }
    //             }
    //         }

    //         /* ================= LOCAL RETURN (DISPLAY ONLY) ================= */
    //         $returnPCS = 0;

    //         foreach (
    //             DB::table('sale_returns')
    //                 ->where('sale_type', 'customer')
    //                 ->where('item_names', $item->item_name)
    //                 ->get() as $r
    //         ) {
    //             $pcsVal = (int) ($r->pcs_qty ?? 0);
    //             $cartonVal = (int) ($r->carton_qty ?? 0);

    //             if ($pcsVal > 0) {
    //                 // PCS based return
    //                 $returnPCS += $pcsVal;
    //             } else {
    //                 // Carton based return → convert using product pcs_in_carton
    //                 $returnPCS += ($cartonVal * $pcsInCarton);
    //             }
    //         }

    //         /* ================= FINAL STOCK ================= */
    //         /**
    //          * 🔥 IMPORTANT RULE:
    //          * product.initial_stock is ALREADY UPDATED
    //          * on SALE (minus) and RETURN (plus)
    //          * so report me kuch add / minus nahi karna
    //          */
    //         /* ================= STOCK VALUE (PCS BASED) ================= */
    //         $pcsInCarton = max((int) $item->pcs_in_carton, 1);
    //         $initialStock = (int) ($item->initial_stock ?? 0);
    //         $wholesalePrice = (float) ($item->wholesale_price ?? 0);

    //         /* per pcs price */
    //         // $perPcsPrice = $wholesalePrice / $pcsInCarton;
    //         $perPcsPrice = $wholesalePrice * $initialStock;

    //         /* total stock value */
    //         $item->stock_value = round($perPcsPrice);

    //         /* simple balance (as decided) */
    //         $item->balance_stock = $initialStock;
    //         $item->balance_wholesale_price = $wholesalePrice;

    //         /* ================= DISPLAY HELPERS ================= */
    //         $item->total_local_sold_carton = intdiv($soldPCS, $pcsInCarton);
    //         $item->total_local_sold_pcs = $soldPCS * $pcsInCarton;

    //         $item->total_local_return_carton = intdiv($returnPCS, $pcsInCarton);
    //         $item->total_local_return_pcs = $returnPCS * $pcsInCarton;

    //     }

    //     return response()->json($items);
    // }


    public function date_wise_recovery_report()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Customers = Customer::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure

            $Salesmans = Salesman::where('admin_or_user_id', $userId)
                ->where('designation', 'Saleman')
                ->get();

            return view('admin_panel.reports.date_wise_recovery_report', [
                'Customers' => $Customers,
                'Salesmans' => $Salesmans,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function getRecoveryReport(Request $request)
    {
        $salesman = $request->salesman;
        $type = $request->type;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $recoveries = [];

        // Distributor Recoveries
        if ($type == 'all' || $type == 'distributor') {
            $query = Recovery::whereBetween('date', [$startDate, $endDate]);

            if ($salesman !== 'All') {
                $query->where('salesman', $salesman);
            }

            $distributorRecoveries = $query->get();

            foreach ($distributorRecoveries as $recovery) {
                $distributor = Distributor::find($recovery->distributor_ledger_id);
                $recoveries[] = [
                    'date' => $recovery->date,
                    'shop_name' => '-', // Distributors ke liye shop name nahi hota
                    'party_name' => $distributor->Customer ?? 'N/A',
                    'area' => $distributor->Area ?? 'N/A',
                    'remarks' => $recovery->remarks,
                    'amount_paid' => number_format($recovery->amount_paid),
                    'salesman' => $recovery->salesman ?? '-',
                ];
            }
        }

        // Customer Recoveries
        if ($type == 'all' || $type == 'customer') {
            $query = CustomerRecovery::whereBetween('date', [$startDate, $endDate]);

            if ($salesman !== 'All') {
                $query->where('salesman', $salesman);
            }

            $customerRecoveries = $query->get();

            foreach ($customerRecoveries as $recovery) {
                $customer = DB::table('customers')
                    ->join('customer_ledgers', 'customer_ledgers.customer_id', '=', 'customers.id')
                    ->where('customer_ledgers.id', $recovery->customer_ledger_id)
                    ->select('customers.shop_name', 'customers.customer_name', 'customers.area')
                    ->first();
                $recoveries[] = [
                    'date' => $recovery->date,
                    'shop_name' => $customer->shop_name ?? 'N/A',
                    'party_name' => $customer->customer_name ?? 'N/A',
                    'area' => $customer->area ?? 'N/A',
                    'remarks' => $recovery->remarks,
                    'amount_paid' => number_format($recovery->amount_paid),
                    'salesman' => $recovery->salesman ?? '-',
                ];

            }
        }

        return response()->json($recoveries);
    }

    public function date_wise_purcahse_report()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Customers = Customer::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure

            return view('admin_panel.reports.date_wise_purcahse_report', [
                'Customers' => $Customers,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function fetch_purchase_report(Request $request)
    {
        $userId = Auth::id();
        $start = $request->start_date;
        $end = $request->end_date;

        $purchases = Purchase::where('admin_or_user_id', $userId)
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $report = [];
        $totals = [
            'carton' => 0,
            'pcs' => 0,
            'liter' => 0,
            'net_amount' => 0,
        ];

        foreach ($purchases as $key => $purchase) {
            $items = json_decode($purchase->item ?? '[]');
            $pcs_carton = json_decode($purchase->pcs_carton ?? '[]');
            $carton_qty = json_decode($purchase->carton_qty ?? '[]');
            $pcs = json_decode($purchase->pcs ?? '[]');
            $liter = json_decode($purchase->liter ?? '[]');
            $amounts = json_decode($purchase->amount ?? '[]'); // 👈 Use amount field here

            foreach ($items as $i => $item) {
                $netAmount = floatval($amounts[$i] ?? 0);

                $report[] = [
                    'code' => $key + 1,
                    'date' => \Carbon\Carbon::parse($purchase->purchase_date)->format('d-M-Y'),
                    'item' => $item ?? 'N/A',
                    'carton_packing' => $pcs_carton[$i] ?? 0,
                    'carton_qty' => $carton_qty[$i] ?? 0,
                    'pcs' => $pcs[$i] ?? 0,
                    'liter' => $liter[$i] ?? 0,
                    'net_amount' => $netAmount,
                ];

                $totals['carton'] += floatval($carton_qty[$i] ?? 0);
                $totals['pcs'] += floatval($pcs[$i] ?? 0);
                $totals['liter'] += floatval($liter[$i] ?? 0);
                $totals['net_amount'] += $netAmount;
            }
        }

        return response()->json([
            'report' => $report,
            'totals' => $totals,
        ]);
    }

    public function vendor_wise_purcahse_report()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Vendors = Vendor::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure

            return view('admin_panel.reports.vendor_wise_purcahse_report', [
                'Vendors' => $Vendors,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function fetchVendorPurchaseReport(Request $request)
    {
        $userId = Auth::id();
        $start = $request->start_date;
        $end = $request->end_date;
        $vendorId = $request->vendor_id;

        if (! $vendorId) {
            return response()->json(['error' => 'Vendor is required'], 422);
        }

        $purchases = Purchase::where('admin_or_user_id', $userId)
            ->where('party_name', $vendorId) // ✅ match by vendor ID in party_name
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $report = [];
        $totals = [
            'carton' => 0,
            'pcs' => 0,
            'liter' => 0,
            'net_amount' => 0,
        ];

        foreach ($purchases as $key => $purchase) {
            $items = json_decode($purchase->item ?? '[]');
            $pcs_carton = json_decode($purchase->pcs_carton ?? '[]');
            $carton_qty = json_decode($purchase->carton_qty ?? '[]');
            $pcs = json_decode($purchase->pcs ?? '[]');
            $liter = json_decode($purchase->liter ?? '[]');
            $amounts = json_decode($purchase->amount ?? '[]'); // ✅ new line

            foreach ($items as $i => $item) {
                $netAmount = floatval($amounts[$i] ?? 0);

                $report[] = [
                    'inv_no' => $purchase->invoice_number ?? 'N/A',
                    'date' => \Carbon\Carbon::parse($purchase->purchase_date)->format('d-M-Y'),
                    'item' => $item ?? 'N/A',
                    'carton_packing' => $pcs_carton[$i] ?? 0,
                    'carton_qty' => $carton_qty[$i] ?? 0,
                    'pcs' => $pcs[$i] ?? 0,
                    'liter' => $liter[$i] ?? 0,
                    'net_amount' => $netAmount,
                ];

                $totals['carton'] += floatval($carton_qty[$i] ?? 0);
                $totals['pcs'] += floatval($pcs[$i] ?? 0);
                $totals['liter'] += floatval($liter[$i] ?? 0);
                $totals['net_amount'] += $netAmount;
            }
        }

        return response()->json([
            'report' => $report,
            'totals' => $totals,
        ]);
    }

    public function contractor_wise_report()
    {

        if (Auth::id()) {
            $userId = Auth::id();
            // Assuming you have a Contractor model
            $Contractors = Contractor::where('admin_or_user_id', $userId)->get();

            return view('admin_panel.reports.contractor_wise_report', [
                'Contractors' => $Contractors,
            ]);
        } else {
            return redirect()->back();
        }
    }

  public function fetchContractorReport(Request $request)
{
    $userId = Auth::id();
    $start = $request->start_date;
    $end = $request->end_date;
    $contractorId = $request->contractor_id;

    if (!$contractorId) {
        return response()->json(['error' => 'Contractor is required'], 422);
    }

    // ✅ Filter by contractor_id
    $jobs = JobOrder::where('admin_or_user_id', $userId)
        ->where('staff_type', 'contract')
        ->where('staff_id', $contractorId)  // ✅ Add this filter
        ->whereBetween('job_date', [$start, $end])
        ->orderBy('job_date')
        ->get();

    $report = [];
    foreach ($jobs as $job) {
        // ✅ Parse work_type JSON and extract names
        $workTypes = [];
        if ($job->work_type) {
            $decoded = json_decode($job->work_type, true);
            if (is_array($decoded)) {
                foreach ($decoded as $work) {
                    if (isset($work['name'])) {
                        $workTypes[] = $work['name'];
                    }
                }
            }
        }
        
        // ✅ Join work types with comma
        $workTypeString = !empty($workTypes) ? implode(', ', $workTypes) : 'N/A';

        $report[] = [
            'job_no' => $job->job_order_no,
            'date' => \Carbon\Carbon::parse($job->job_date)->format('d-M-Y'),
            'work_type' => $workTypeString,  // ✅ Now it's comma separated
            'total_amount' => $job->total_amount,
            'paid_amount' => $job->paid_amount,
            'remaining_amount' => $job->remaining_amount,
            'status' => ucfirst($job->status),
        ];
    }

    return response()->json([
        'report' => $report,
        'totals' => [
            'total_amount' => $jobs->sum('total_amount'),
            'paid_amount' => $jobs->sum('paid_amount'),
            'remaining_amount' => $jobs->sum('remaining_amount'),
        ],
    ]);
}

    public function staff_wise_report()
{
    $staffs = Salesman::where('admin_or_user_id', Auth::id())
        ->where('status', 1)
        ->get();

    return view('admin_panel.reports.staff_wise_report', compact('staffs'));
}

public function fetchStaffReport(Request $request)
{
    $userId = Auth::id();
    $staffId = $request->staff_id;

    if (! $staffId) {
        return response()->json(['error' => 'Staff is required'], 422);
    }

    // ================= STAFF =================
    $staff = DB::table('sales_mens')
        ->where('id', $staffId)
        ->where('admin_or_user_id', $userId)
        ->first();

    if (! $staff) {
        return response()->json(['error' => 'Staff not found'], 404);
    }

    // ================= LEDGER =================
    $ledger = DB::table('staff_ledgers')
        ->where('saleman_id', $staffId)
        ->where('admin_or_user_id', $userId)
        ->orderBy('ledger_date', 'desc')
        ->get();

    $totals = [
        'opening' => 0,
        'previous' => 0,
        'closing' => 0,
    ];

    if ($ledger->count()) {
        $totals['opening'] = $ledger->first()->opening_balance;
        $totals['previous'] = $ledger->first()->previous_balance;
        $totals['closing'] = $ledger->first()->closing_balance;
    }

    return response()->json([
        'staff' => [
            'name' => $staff->name,
            'phone' => $staff->phone,
            'designation' => $staff->designation,
            'salary' => (float) $staff->salary,
            'city' => $staff->city,
            'address' => $staff->address,
        ],
        'ledger' => $ledger,
        'totals' => $totals,
    ]);
}

public function staffWeeklyHistory(Request $request)
{
    return DB::table('staff_ledgers')
        ->where('saleman_id', $request->staff_id)
        ->where('admin_or_user_id', Auth::id())
        ->whereNotNull('week_start')  // ✅ null entries filter
        ->whereNotNull('week_end')
        ->orderBy('week_start', 'desc')
        ->get();
}

// ✅ New function
public function saveStaffWeekly(Request $request)
{
    $userId = Auth::id();
    $staffId = $request->staff_id;

    // Get previous balance
    $previousEntry = DB::table('staff_ledgers')
        ->where('saleman_id', $staffId)
        ->where('admin_or_user_id', $userId)
        ->whereNotNull('week_start')
        ->orderBy('week_end', 'desc')
        ->first();

    $previousBalance = $previousEntry ? (float)$previousEntry->balance : 0;

    // Calculate new balance
    $weeklyAmount = (float)$request->weekly_amount;
    $paid = (float)($request->paid ?? 0);
    $advance = (float)($request->advance ?? 0);

    // Previous Balance + Weekly - Paid - Advance
    $newBalance = $previousBalance + $weeklyAmount - $paid - $advance;

    // Save
    DB::table('staff_ledgers')->insert([
        'admin_or_user_id' => $userId,
        'saleman_id' => $staffId,
        'week_start' => $request->week_start,
        'week_end' => $request->week_end,
        'weekly_amount' => $weeklyAmount,
        'paid' => $paid,
        'advance' => $advance,
        'balance' => $newBalance,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return response()->json(['success' => true]);
}

    public function Area_wise_Customer_payments()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Customers = Customer::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure
            $cities = City::all(); // Updated the variable name to avoid confusion

            $Salesmans = Salesman::where('admin_or_user_id', $userId)
                ->where('designation', 'Saleman')
                ->get();

            return view('admin_panel.reports.Area_wise_Customer_payments', [
                'Customers' => $Customers,
                'cities' => $cities,
                'Salesmans' => $Salesmans,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function staffWeeklySave(Request $request)
    {
        $request->validate([
            'staff_id' => 'required',
            'week_start' => 'required|date',
            'week_end' => 'required|date',
        ]);

        DB::table('staff_ledgers')->insert([
            'admin_or_user_id' => Auth::id(),
            'saleman_id' => $request->staff_id,
            'week_start' => $request->week_start,
            'week_end' => $request->week_end,
            'weekly_amount' => $request->weekly_amount ?? 0,
            'paid' => $request->paid ?? 0,
            'advance' => $request->advance ?? 0,
            'balance' => $request->balance ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function fetchReceivableReport(Request $request)
    {
        $cities = $request->city ?? [];  // multiple cities array
        $areas = $request->area;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $salesman = $request->salesman;

        if (in_array('All', (array) $cities)) {
            $customerCities = DB::table('customers')->select('city')->distinct()->pluck('city')->toArray();
            $distributorCities = DB::table('distributors')->select('City')->distinct()->pluck('City')->toArray();
            $allCities = array_unique(array_merge($customerCities, $distributorCities));
        } else {
            $allCities = (array) $cities; // multiple cities handle
        }

        $result = [];

        foreach ($allCities as $c) {
            // ==== CUSTOMERS ====
            $customersQuery = DB::table('customers')->where('city', $c);

            if (! empty($areas) && ! in_array('All', (array) $areas)) {
                $customersQuery->whereIn('area', (array) $areas);
            }

            $customers = $customersQuery->get();
            $customerData = [];

            foreach ($customers as $customer) {
                $customerHasSalesBySalesman = DB::table('local_sales')
                    ->where('customer_id', $customer->id)
                    ->when($salesman !== 'All', fn ($query) => $query->where('Saleman', $salesman))
                    ->exists();

                if ($salesman !== 'All' && ! $customerHasSalesBySalesman) {
                    continue;
                }

                $ledger = DB::table('customer_ledgers')->where('customer_id', $customer->id)->first();
                $openingBalance = $ledger->opening_balance ?? 0;

                $totalSales = DB::table('local_sales')
                    ->where('customer_id', $customer->id)
                    ->whereBetween('Date', [$startDate, $endDate])
                    ->when($salesman !== 'All', fn ($query) => $query->where('Saleman', $salesman))
                    ->sum('grand_total');

                $totalReturns = DB::table('sale_returns')
                    ->where('sale_type', 'customer')
                    ->where('party_id', $customer->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->when($salesman !== 'All', function ($query) use ($salesman) {
                        return $query->whereExists(function ($subquery) use ($salesman) {
                            $subquery->select(DB::raw(1))
                                ->from('local_sales')
                                ->whereColumn('local_sales.customer_id', 'sale_returns.party_id')
                                ->where('local_sales.Saleman', $salesman);
                        });
                    })
                    ->sum('total_return_amount');

                $totalRecoveries = DB::table('customer_recoveries as cr')
                    ->join('customer_ledgers as cl', 'cl.id', '=', 'cr.customer_ledger_id')
                    ->where('cl.customer_id', $customer->id)
                    ->when($salesman !== 'All', fn ($q) => $q->where('cr.salesman', $salesman))
                    ->whereBetween('cr.date', [$startDate, $endDate])
                    ->sum('cr.amount_paid');

                $balance = ($openingBalance + $totalSales - $totalReturns) - $totalRecoveries;

                if (round($balance, 2) != 0 || $totalSales > 0 || $totalReturns > 0 || $totalRecoveries > 0) {
                    $customerData[] = [
                        'type' => 'customer',
                        'pcode' => $customer->id,
                        'name' => $customer->customer_name,
                        'shopname' => $customer->shop_name,
                        'address' => $customer->area,
                        'contact' => $customer->phone_number,
                        'balance' => round($balance, 2),
                    ];
                }
            }

            // ==== DISTRIBUTORS ====
            $distributorQuery = DB::table('distributors')->where('City', $c);

            if (! empty($areas) && ! in_array('All', (array) $areas)) {
                $distributorQuery->whereIn('Area', (array) $areas);
            }

            if ($salesman !== 'All') {
                $distributorQuery->whereExists(function ($query) use ($salesman) {
                    $query->select(DB::raw(1))
                        ->from('sales')
                        ->whereColumn('sales.distributor_id', 'distributors.id')
                        ->where('sales.Saleman', $salesman);
                });
            }

            $distributors = $distributorQuery->get();
            $distributorData = [];

            foreach ($distributors as $distributor) {
                $distributorHasSalesBySalesman = DB::table('sales')
                    ->where('distributor_id', $distributor->id)
                    ->when($salesman !== 'All', fn ($query) => $query->where('Saleman', $salesman))
                    ->exists();

                if ($salesman !== 'All' && ! $distributorHasSalesBySalesman) {
                    continue;
                }

                $ledger = DB::table('distributor_ledgers')->where('distributor_id', $distributor->id)->first();
                $openingBalance = $ledger->opening_balance ?? 0;

                $totalSales = DB::table('sales')
                    ->where('distributor_id', $distributor->id)
                    ->whereBetween('Date', [$startDate, $endDate])
                    ->when($salesman !== 'All', fn ($query) => $query->where('Saleman', $salesman))
                    ->sum('grand_total');

                $totalReturns = DB::table('sale_returns')
                    ->where('sale_type', 'distributor')
                    ->where('party_id', $distributor->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->when($salesman !== 'All', function ($query) use ($salesman) {
                        return $query->whereExists(function ($subquery) use ($salesman) {
                            $subquery->select(DB::raw(1))
                                ->from('sales')
                                ->whereColumn('sales.distributor_id', 'sale_returns.party_id')
                                ->where('sales.Saleman', $salesman);
                        });
                    })
                    ->sum('total_return_amount');

                $totalRecoveries = DB::table('recoveries')
                    ->where('distributor_ledger_id', $distributor->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->when($salesman !== 'All', fn ($query) => $query->where('salesman', $salesman))
                    ->sum('amount_paid');

                $balance = ($openingBalance + $totalSales - $totalReturns) - $totalRecoveries;

                if (round($balance, 2) != 0 || $totalSales > 0 || $totalReturns > 0 || $totalRecoveries > 0) {
                    $distributorData[] = [
                        'type' => 'distributor',
                        'pcode' => $distributor->id,
                        'name' => $distributor->Customer,
                        'address' => $distributor->Area,
                        'contact' => $distributor->Contact,
                        'balance' => round($balance, 2),
                    ];
                }
            }

            $result[$c] = [
                'distributors' => $distributorData,
                'customers' => $customerData,
            ];
        }

        return response()->json([
            'data' => $result,
            'salesman_name' => ($salesman !== 'All') ? $salesman : 'All Salesmen',
        ]);
    }

    public function Area_wise_salesman_market_payments()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Customers = Customer::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure
            $cities = City::all(); // Updated the variable name to avoid confusion

            $Salesmans = Salesman::where('admin_or_user_id', $userId)
                ->where('designation', 'Saleman')
                ->get();

            return view('admin_panel.reports.Area_wise_salesman_market_payments', [
                'Customers' => $Customers,
                'cities' => $cities,
                'Salesmans' => $Salesmans,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function receivablesalesmanmarketreport(Request $request)
    {
        $salesmanFilter = $request->input('salesman');
        $city = $request->input('city');
        $areas = $request->input('area');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $result = [];

        // Get all salesmen or specific one
        $salesmen = ($salesmanFilter === 'All')
            ? Salesman::where('designation', 'Saleman')->pluck('name')
            : collect([$salesmanFilter]);

        foreach ($salesmen as $salesman) {
            $salesmanData = [];

            $customers = DB::table('customers')
                ->join('local_sales', 'customers.id', '=', 'local_sales.customer_id')
                ->where('local_sales.Saleman', $salesman)
                ->where('customers.identify', 'admin') // Or your dynamic identity logic
                ->when($city !== 'All', function ($q) use ($city) {
                    $q->where('customers.city', $city);
                })
                ->when(! empty($areas) && $city !== 'All', function ($q) use ($areas) {
                    $q->whereIn('customers.area', $areas);
                })
                ->select('customers.*')
                ->distinct()
                ->get();

            foreach ($customers as $customer) {
                // Get latest ledger
                $ledger = DB::table('customer_ledgers')
                    ->where('customer_id', $customer->id)
                    ->latest('created_at')
                    ->first();

                $openingBalance = $ledger->opening_balance ?? 0;
                $previousBalance = $ledger->previous_balance ?? 0;
                $closingBalance = $ledger->closing_balance ?? 0;

                $totalSales = DB::table('local_sales')
                    ->where('customer_id', $customer->id)
                    ->where('Saleman', $salesman)
                    ->whereBetween('Date', [$startDate, $endDate])
                    ->sum('grand_total');

                $totalReturns = DB::table('sale_returns')
                    ->where('sale_type', 'customer')
                    ->where('party_id', $customer->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('total_return_amount');

                $totalRecoveries = DB::table('customer_recoveries as cr')
                    ->join('customer_ledgers as cl', 'cl.id', '=', 'cr.customer_ledger_id')
                    ->where('cl.customer_id', $customer->id)
                    ->where('cr.salesman', $salesman)
                    ->whereBetween('cr.date', [$startDate, $endDate])
                    ->sum('cr.amount_paid');

                $balance = ($openingBalance + $totalSales - $totalReturns) - $totalRecoveries;

                // Group by city > area
                $salesmanData[$customer->city][$customer->area][] = [
                    'customer_name' => $customer->customer_name,
                    'shop_name' => $customer->shop_name,
                    'phone' => $customer->phone_number,
                    'opening_balance' => round($openingBalance, 2),
                    'previous_balance' => round($previousBalance, 2),
                    'closing_balance' => round($closingBalance, 2),
                    'total_sales' => round($totalSales, 2),
                    'total_returns' => round($totalReturns, 2),
                    'total_recoveries' => round($totalRecoveries, 2),
                    'balance' => round($balance, 2),
                ];
            }

            $result[$salesman] = $salesmanData;
        }

        return response()->json($result);
    }

    public function Date_wise_Sales_Report()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Customers = Customer::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure

            $Salesmans = Salesman::where('admin_or_user_id', $userId)
                ->where('designation', 'Saleman')
                ->get();

            return view('admin_panel.reports.Date_wise_Sales_Report', [
                'Customers' => $Customers,
                'Salesmans' => $Salesmans,
            ]);
        } else {
            return redirect()->back();
        }
    }
    // public function getsalesreport(Request $request)
    // {
    //     $salesman = $request->salesman;
    //     $type = $request->type;
    //     $startDate = $request->start_date;
    //     $endDate = $request->end_date;

    //     $sales = [];

    //     // --- Distributor Sales ---
    //     if ($type == 'all' || $type == 'distributor') {
    //         $query = DB::table('sales')->whereBetween('Date', [$startDate, $endDate]);

    //         if ($salesman !== 'All') {
    //             $query->where('Saleman', $salesman);
    //         }

    //         $results = $query->get();

    //         $distributorIds = $results->pluck('distributor_id')->unique()->filter()->values();

    //         $distributorMap = DB::table('distributors')
    //             ->whereIn('id', $distributorIds)
    //             ->pluck('Customer', 'id');

    //         foreach ($results as $row) {
    //             $items = json_decode($row->cart_items, true) ?? [];
    //             $cartons = json_decode($row->carton_qty, true) ?? [];
    //             $pcs = json_decode($row->pcs, true) ?? [];

    //             $itemDetails = [];
    //             foreach ($items as $i => $itm) {
    //                 $itemDetails[] = $itm . " (" . ($cartons[$i] ?? 0) . " CTN, " . ($pcs[$i] ?? 0) . " PCS)";
    //             }

    //             $distributorName = $distributorMap[$row->distributor_id] ?? 'Distributor-' . $row->distributor_id;

    //             $sales[] = [
    //                 'invoice_number' => $row->invoice_number,
    //                 'date' => $row->Date,
    //                 'party_name' => $distributorName,
    //                 'area' => $row->distributor_area ?? 'N/A',
    //                 'remarks' => 'Distributor Sale',
    //                 'items' => implode(", ", $itemDetails),
    //                 'amount_paid' => number_format($row->net_amount),
    //                 'salesman' => $row->Saleman ?? '-'
    //             ];
    //         }
    //     }

    //     // --- Customer Sales ---
    //     if ($type == 'all' || $type == 'customer') {
    //         $query = DB::table('local_sales')->whereBetween('Date', [$startDate, $endDate]);

    //         if ($salesman !== 'All') {
    //             $query->where('Saleman', $salesman);
    //         }

    //         $results = $query->get();

    //         foreach ($results as $row) {
    //             $items = json_decode($row->cart_items, true) ?? [];
    //             $cartons = json_decode($row->carton_qty, true) ?? [];
    //             $pcs = json_decode($row->pcs, true) ?? [];

    //             $itemDetails = [];
    //             foreach ($items as $i => $itm) {
    //                 $itemDetails[] = $itm . " (" . ($cartons[$i] ?? 0) . " CTN, " . ($pcs[$i] ?? 0) . " PCS)";
    //             }

    //             $sales[] = [
    //                 'invoice_number' => $row->invoice_number,
    //                 'date' => $row->Date,
    //                 'party_name' => $row->customer_shopname ?? 'Customer-' . $row->customer_id,
    //                 'area' => $row->customer_area ?? 'N/A',
    //                 'remarks' => 'Customer Sale',
    //                 'items' => implode(", ", $itemDetails),
    //                 'amount_paid' => number_format($row->net_amount),
    //                 'salesman' => $row->Saleman ?? '-'
    //             ];
    //         }
    //     }

    //     return response()->json($sales);
    // }

    public function getsalesreport(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        // If no dates provided, use today's date
        if (empty($startDate) || empty($endDate)) {
            $startDate = Carbon::today()->startOfDay();
            $endDate = Carbon::today()->endOfDay();
        }

        $results = DB::table('local_sales')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $sales = [];

        foreach ($results as $row) {
            $items = json_decode($row->cart_items, true) ?? [];
            $cartons = json_decode($row->carton_qty, true) ?? [];
            $pcs = json_decode($row->pcs, true) ?? [];

            $itemDetails = [];
            foreach ($items as $i => $itm) {
                $itemDetails[] = $itm.' ('.($cartons[$i] ?? 0).' CTN, '.($pcs[$i] ?? 0).' PCS)';
            }

            $sales[] = [
                'invoice_number' => $row->invoice_number,
                'date' => $row->Date,
                'party_name' => $row->customer_shopname ?? 'Customer-'.$row->customer_id,
                'area' => $row->customer_area ?? 'N/A',
                'remarks' => 'Local Sale',
                'items' => implode(', ', $itemDetails),
                'amount_paid' => number_format($row->net_amount),
            ];
        }

        return response()->json($sales);
    }

    public function Product_wise_Sales_Report()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Products = Product::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure

            return view('admin_panel.reports.Product_wise_Sales_Report', [
                'Products' => $Products,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function getProductsalesreport(Request $request)
    {
        $products = $request->Product;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $sales = [];

        // --- Distributor Sales ---
        $query = DB::table('local_sales')->whereBetween('created_at', [$startDate, $endDate]);
        $results = $query->get();

        $distributorIds = $results->pluck('distributor_id')->unique()->filter()->values();
        $distributorMap = DB::table('distributors')
            ->whereIn('id', $distributorIds)
            ->pluck('Customer', 'id');

        foreach ($results as $row) {
            $items = json_decode($row->cart_items, true) ?? [];
            $cartons = json_decode($row->carton_qty, true) ?? [];
            $pcs = json_decode($row->pcs, true) ?? [];
            $amounts = json_decode($row->amount, true) ?? [];
            $liters = property_exists($row, 'liter') ? (json_decode($row->liter, true) ?? []) : [];

            foreach ($items as $i => $itm) {
                // ❌ Null ya "Select Item" skip karo
                if (empty($itm) || strtolower($itm) == 'select item') {
                    continue;
                }

                // Agar filter laga hai to "All" ke ilawa wahi products allow hongay
                if (! in_array('All', $products) && ! in_array($itm, $products)) {
                    continue;
                }

                if (! isset($sales[$itm])) {
                    $sales[$itm] = [
                        'item' => $itm,
                        'carton_qty' => 0,
                        'pcs' => 0,
                        'liters' => 0,
                        'amount' => 0,
                    ];
                }$items = json_decode($row->item, true);

                $sales[$itm]['carton_qty'] += $cartons[$i] ?? 0;
                $sales[$itm]['pcs'] += $pcs[$i] ?? 0;
                $sales[$itm]['liters'] += $liters[$i] ?? 0;
                $sales[$itm]['amount'] += $amounts[$i] ?? 0;
            }
        }

        // --- Customer Sales ---
        $query = DB::table('local_sales')->whereBetween('Date', [$startDate, $endDate]);
        $results = $query->get();

        foreach ($results as $row) {
            $items = json_decode($row->cart_items, true) ?? [];
            $cartons = json_decode($row->carton_qty, true) ?? [];
            $pcs = json_decode($row->pcs, true) ?? [];
            $amounts = json_decode($row->amount, true) ?? [];
            $liters = property_exists($row, 'liter') ? (json_decode($row->liter, true) ?? []) : [];

            foreach ($items as $i => $itm) {
                // ❌ Null ya "Select Item" skip karo
                if (empty($itm) || strtolower($itm) == 'select item') {
                    continue;
                }

                if (! in_array('All', $products) && ! in_array($itm, $products)) {
                    continue;
                }

                if (! isset($sales[$itm])) {
                    $sales[$itm] = [
                        'item' => $itm,
                        'carton_qty' => 0,
                        'pcs' => 0,
                        'liters' => 0,
                        'amount' => 0,
                    ];
                }

                $sales[$itm]['carton_qty'] += $cartons[$i] ?? 0;
                $sales[$itm]['pcs'] += $pcs[$i] ?? 0;
                $sales[$itm]['liters'] += $liters[$i] ?? 0;
                $sales[$itm]['amount'] += $amounts[$i] ?? 0;
            }
        }

        return response()->json(array_values($sales));
    }
    // public function revenue_Record()
    // {
    //     // Totals
    //     $totalSale = 0;
    //     $totalCost = 0;
    //     $totalProfit = 0;
    //     $totalUnitProfit = 0;

    //     $productSummary = [];

    //     // Fetch all sales
    //     $sales = LocalSale::all();

    //     foreach ($sales as $sale) {
    //         $items = json_decode($sale->item, true);
    //         $codes = json_decode($sale->code, true);
    //         $amounts = json_decode($sale->amount, true);
    //         $quantities = json_decode($sale->pcs, true);
    //         $returns = json_decode($sale->return_quantity ?? '[]', true);

    //         if ($items && is_array($items)) {
    //             foreach ($items as $index => $itemName) {
    //                 $productId = $codes[$index] ?? null;
    //                 $saleAmount = $amounts[$index] ?? 0;
    //                 $quantity = $quantities[$index] ?? 0;
    //                 $returnQty = $returns[$index] ?? 0;

    //                 $product = Product::find($productId);
    //                 if (!$product) continue;

    //                 $wholesalePrice = $product->wholesale_price ?? 0;
    //                 $retailPrice = $product->retail_price ?? 0;

    //                 $cost = ($wholesalePrice * $quantity) - ($wholesalePrice * $returnQty);
    //                 $adjustedSaleAmount = $saleAmount - ($retailPrice * $returnQty);
    //                 $profit = $adjustedSaleAmount - $cost;

    //                 $totalSale += $adjustedSaleAmount;
    //                 $totalCost += $cost;
    //                 $totalProfit += $profit;

    //                 if (!isset($productSummary[$productId])) {
    //                     $productSummary[$productId] = [
    //                         'name' => $product->item_name,
    //                         'category' => $product->category,
    //                         'sub_category' => $product->sub_category,
    //                         'quantity' => 0,
    //                         'unit_wholesale' => $wholesalePrice,
    //                         'unit_retail' => $retailPrice,
    //                         'total_sale' => 0,
    //                         'total_cost' => 0,
    //                         'profit' => 0,
    //                         'total_return' => 0,
    //                     ];
    //                 }

    //                 $productSummary[$productId]['quantity'] += $quantity;
    //                 $productSummary[$productId]['total_return'] += $returnQty;
    //                 $productSummary[$productId]['total_sale'] += $adjustedSaleAmount;
    //                 $productSummary[$productId]['total_cost'] += $cost;
    //                 $productSummary[$productId]['profit'] += $profit;
    //             }
    //         }
    //     }

    //     // ✅ Total Unit Profit (sum of each product's unit profit)
    //     foreach ($productSummary as $product) {
    //         $unitProfit = $product['unit_retail'] - $product['unit_wholesale'];
    //         $totalUnitProfit += $unitProfit;
    //     }

    //     $netProfit = $totalProfit;

    //     return view('admin_panel.reports.revenue_record', compact(
    //         'totalSale', 'totalCost', 'totalProfit', 'netProfit', 'productSummary', 'totalUnitProfit'
    //     ));
    // }

    public function vendorStockRecord()
    {
        if (! Auth::id()) {
            return redirect()->back();
        }

        $userId = Auth::id();

        $categories = Category::where('admin_or_user_id', $userId)->get();
        $vendors = DB::table('vendors')
            ->where('admin_or_user_id', $userId)
            ->select('id', 'Party_code', 'Party_name')
            ->orderBy('Party_name')
            ->get();

        return view('admin_panel.reports.stock_Record_vendor', [
            'categories' => $categories,
            'vendors' => $vendors,
        ]);
    }

    // Items by subcategory (same as before, vendor-agnostic)
    public function getVendorItems($subcategory)
    {
        $items = Product::where('sub_category', $subcategory)
            ->select('item_code', 'item_name')->get();

        return response()->json($items);
    }

    // ===== Helper to sum cartons from JSON arrays for a matched $needle item =====
    private function sumFromJsonRows($rows, $itemCol, $qtyCol, $needle)
    {
        $sum = 0;
        foreach ($rows as $row) {
            $items = json_decode($row->$itemCol, true);
            $qtys = json_decode($row->$qtyCol, true);

            if (is_array($items) && is_array($qtys)) {
                foreach ($items as $i => $name) {
                    if ($name === $needle) {
                        $sum += isset($qtys[$i]) ? intval($qtys[$i]) : 0;
                    }
                }
            }
        }

        return $sum;
    }

    // ===== Helper to sum "pcs to liters" if ever needed (we're using cartons here) =====
    private function litersFor(Product $p, $cartons)
    {
        // size like "120ml" or "1 liter"
        $sizeText = strtolower(trim($p->size ?? ''));
        $perPieceL = 0;

        if (str_contains($sizeText, 'ml')) {
            $num = floatval(preg_replace('/[^0-9.]/', '', $sizeText));
            $perPieceL = $num / 1000.0;
        } elseif (str_contains($sizeText, 'liter') || preg_match('/\b\d+(\.\d+)?l\b/', $sizeText)) {
            $num = floatval(preg_replace('/[^0-9.]/', '', $sizeText));
            $perPieceL = $num;
        } else {
            $perPieceL = floatval($sizeText) ?: 0;
        }

        $pcsPerCarton = intval($p->pcs_in_carton ?: 0);

        return $perPieceL * $pcsPerCarton * $cartons;
    }

    // ===== MAIN: Vendor-wise filtered data =====
    public function getVendorItemDetailsVendorWise(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'category' => 'required',
            'subcategory' => 'required',
            'itemCode' => 'required',
        ]);

        $userId = Auth::id();
        $vendorId = (int) $request->vendor_id;

        $vendor = DB::table('vendors')
            ->where('admin_or_user_id', $userId)
            ->where('id', $vendorId)
            ->first();

        if (! $vendor) {
            return response()->json([]);
        }

        $vendorPartyCode = $vendor->Party_code;

        /* ========= 1️⃣ GET ONLY VENDOR PURCHASED ITEMS ========= */
        $vendorItemNames = Purchase::where('admin_or_user_id', $userId)
            ->where('party_code', $vendorPartyCode)
            ->pluck('item')
            ->toArray();

        $allowedItems = [];

        foreach ($vendorItemNames as $json) {
            $items = json_decode($json, true) ?? [];
            foreach ($items as $it) {
                $allowedItems[] = trim($it);
            }
        }

        $allowedItems = array_unique($allowedItems);

        if (empty($allowedItems)) {
            return response()->json([]);
        }

        /* ========= 2️⃣ PRODUCTS (ONLY THOSE ITEMS) ========= */
        $q = Product::where('admin_or_user_id', $userId)
            ->whereIn('item_name', $allowedItems);

        if ($request->category !== 'all') {
            $q->where('category', $request->category);
        }
        if ($request->subcategory !== 'all') {
            $q->where('sub_category', $request->subcategory);
        }
        if ($request->itemCode !== 'all') {
            $q->where('item_code', $request->itemCode);
        }

        $products = $q->get();
        $result = [];

        foreach ($products as $p) {

            $pcsInCarton = max((int) $p->pcs_in_carton, 1);
            $itemName = $p->item_name;

            /* ========= PURCHASE (VENDOR ONLY) ========= */
            $purchasePCS = 0;

            $purchases = Purchase::where('admin_or_user_id', $userId)
                ->where('party_code', $vendorPartyCode)
                ->whereJsonContains('item', $itemName)
                ->get();

            foreach ($purchases as $pur) {
                $names = json_decode($pur->item, true) ?? [];
                $cartons = json_decode($pur->carton_qty, true) ?? [];
                $pcs = json_decode($pur->pcs ?? '[]', true) ?? [];

                foreach ($names as $i => $n) {
                    if ($n === $itemName) {
                        $purchasePCS +=
                            ((int) ($cartons[$i] ?? 0) * $pcsInCarton)
                            + (int) ($pcs[$i] ?? 0);
                    }
                }
            }

            /* ========= PURCHASE RETURN ========= */
            $purchaseReturnPCS = 0;

            $pReturns = DB::table('purchase_returns')
                ->where('admin_or_user_id', $userId)
                ->whereJsonContains('item', $itemName)
                ->get();

            foreach ($pReturns as $pr) {
                $names = json_decode($pr->item, true) ?? [];
                $cartons = json_decode($pr->carton_qty ?? '[]', true) ?? [];
                $pcs = json_decode($pr->return_qty ?? '[]', true) ?? [];

                foreach ($names as $i => $n) {
                    if ($n === $itemName) {
                        $purchaseReturnPCS +=
                            ((int) ($cartons[$i] ?? 0) * $pcsInCarton)
                            + (int) ($pcs[$i] ?? 0);
                    }
                }
            }

            $soldPCS = 0;

            $sales = LocalSale::where('admin_or_user_id', $userId)
                ->whereJsonContains('item', $itemName)
                ->get();

            foreach ($sales as $sale) {
                $names = json_decode($sale->item, true) ?? [];
                $pcs = json_decode($sale->pcs, true) ?? [];

                foreach ($names as $i => $n) {
                    if ($n === $itemName) {
                        $soldPCS += (int) ($pcs[$i] ?? 0);
                    }
                }
            }

            $returnPCS = 0;

            $returns = DB::table('sale_returns')
                ->where('admin_or_user_id', $userId)
                ->where('sale_type', 'customer')
                ->where('item_names', $itemName)
                ->get();

            foreach ($returns as $r) {
                $returnPCS += ((int) $r->pcs_qty > 0)
                    ? (int) $r->pcs_qty
                    : ((int) $r->carton_qty * $pcsInCarton);
            }

            $balanceStock = (int) ($p->initial_stock ?? 0);
            $perPcsPrice = $p->wholesale_price / $pcsInCarton;
            $stockValue = round($perPcsPrice * $balanceStock, 2);

            $result[] = [
                'vendor_name' => $vendor->Party_name,
                'item_code' => $p->item_code,
                'item_name' => $itemName,
                'size' => $p->size,
                'pcs_in_carton' => $pcsInCarton,

                'opening_stock' => $balanceStock,

                'purchased_qty' => $purchasePCS,
                'purchase_return_qty' => $purchaseReturnPCS,

                'sold_local_qty' => $soldPCS,
                'return_local_qty' => $returnPCS,

                'balance_stock' => $balanceStock,
                'w_price' => $p->wholesale_price,
                'stock_value' => $stockValue,
            ];
        }

        return response()->json($result);
    }
}