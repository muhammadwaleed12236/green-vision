<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Stock;
use App\Models\SubCategory;
use App\Models\Vendor;
use App\Models\VendorLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    public function Purchase()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $categories = Category::where('admin_or_user_id', $userId)->get();
            $Vendors = Vendor::where('admin_or_user_id', $userId)->get();

            return view('admin_panel.purchase.add_purchase', compact('categories', 'Vendors'));
        } else {
            return redirect()->back();
        }
    }

    public function getSubcategories($categoryname)
    {
        $subcategories = SubCategory::where('category_name', $categoryname)
            ->pluck('sub_category_name', 'id');

        return response()->json($subcategories);
    }

    public function getItems(Request $request)
    {
        $q = $request->get('q', null);

        $query = Product::query()->select('id', 'item_name', 'retail_price', 'wholesale_price', 'product_mode', 'height', 'width', 'area');

        if ($q === null || $q === '') {
            $items = $query->orderBy('item_name')->limit(200)->get();
        } else {
            $like = "%{$q}%";
            $items = $query->where('item_name', 'like', $like)
                ->orderByRaw("CASE WHEN item_name LIKE '{$q}%' THEN 0 ELSE 1 END, item_name")
                ->limit(100)
                ->get();
        }

        return response()->json($items);
    }

    public function store_Purchase(Request $request)
    {
        // ================= VALIDATION =================
        $request->validate([
            'purchase_date' => 'required|date',
            'party_code' => 'required',
            'party_name' => 'required',
            'grand_total' => 'required|numeric|min:0',
        ]);

        $userId = Auth::id();
        $invoiceNo = Purchase::generateInvoiceNo();

        // ================= ITEMS (FILTER EMPTY ROWS) =================
        $item_names = $request->item_name ?? [];
        $rates = $request->rate ?? [];
        $cartons = $request->product_mode ?? [];
        $pcs = $request->pcs ?? [];
        $discounts = $request->discount ?? [];
        $amounts = $request->amount ?? [];
        $pcs_carton = $request->pcs_carton ?? [];

        $rows = [];

        foreach ($item_names as $i => $name) {
            if (
                trim($name) !== '' ||
                ($rates[$i] ?? 0) > 0 ||
                ($cartons[$i] ?? 0) > 0 ||
                ($pcs[$i] ?? 0) > 0
            ) {
                $rows[] = [
                    'item_name' => $name,
                    'rate' => $rates[$i] ?? 0,
                    'product_mode' => $cartons[$i] ?? 0,
                    'pcs' => $pcs[$i] ?? 0,
                    'discount' => $discounts[$i] ?? 0,
                    'amount' => $amounts[$i] ?? 0,
                    'pcs_carton' => $pcs_carton[$i] ?? 0,
                ];
            }
        }

        if (count($rows) === 0) {
            return back()->withErrors(['items' => 'At least one item is required']);
        }

        // ================= SAVE PURCHASE =================
        $purchase = Purchase::create([
            'admin_or_user_id' => $userId,
            'invoice_number' => $invoiceNo,
            'purchase_date' => $request->purchase_date,
            'party_code' => $request->party_code,
            'party_name' => $request->party_name,
            'item' => json_encode(array_column($rows, 'item_name')),
            'rate' => json_encode(array_column($rows, 'rate')),
            'product_mode' => json_encode(array_column($rows, 'product_mode')),
            'measurement' => json_encode(array_column($rows, 'pcs')),
            'discount' => json_encode(array_column($rows, 'discount')),
            'amount' => json_encode(array_column($rows, 'amount')),
            'pcs_carton' => json_encode(array_column($rows, 'pcs_carton')),
            'grand_total' => $request->grand_total,
        ]);

        // ================= UPDATE PRODUCT STOCK WITH SQ.FT TRACKING =================
        foreach ($rows as $row) {

            $product = Product::where('item_name', $row['item_name'])->first();
            if (! $product) {
                continue;
            }

            $cartonQty = (float) $row['product_mode'];
            $pcsQty = (float) $row['pcs'];
            $rate = (float) $row['rate'];
            $pcsInCarton = (float) $product->pcs_in_carton;

            // Update cartons and pieces
            $product->carton_quantity += $cartonQty;
            $totalPiecesAdded = ($cartonQty * $pcsInCarton) + $pcsQty;
            $product->initial_stock += $totalPiecesAdded;

            // ✅ UPDATE SQUARE FOOTAGE IF PRODUCT HAS AREA
            if ($product->area && $product->area > 0) {
                $addedSqft = $totalPiecesAdded * $product->area;
                $product->total_sqft = ($product->total_sqft ?? 0) + $addedSqft;
            }

            $product->wholesale_price = $rate;
            $product->save();
        }

        // ================= VENDOR LEDGER (FINAL & CORRECT) =================
        $ledger = VendorLedger::where('vendor_id', $request->party_name)->first();

        $currentAmount = $request->grand_total;

        if ($ledger) {

            $previousBalance = $ledger->closing_balance;

            $closingBalance = $previousBalance + $currentAmount;

            $ledger->update([
                'previous_balance' => $previousBalance,
                'closing_balance' => $closingBalance,
            ]);

        } else {

            $openingBalance = 0;
            $previousBalance = $openingBalance;
            $closingBalance = $openingBalance + $currentAmount;

            VendorLedger::create([
                'admin_or_user_id' => $userId,
                'vendor_id' => $request->party_name,
                'opening_balance' => $openingBalance,
                'previous_balance' => $previousBalance,
                'closing_balance' => $closingBalance,
            ]);
        }

        // ================= REDIRECT =================
        return redirect()
            ->route('purchase.invoice', $purchase->id)
            ->with('success', 'Purchase saved & stock updated with Sq.ft tracking successfully');
    }

    public function all_Purchases()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Purchases = Purchase::where('admin_or_user_id', $userId)
                ->with('vendor')
                ->get();

            foreach ($Purchases as $purchase) {
                if (! $purchase->vendor) {
                    logger("Vendor not found for Purchase ID: {$purchase->id}, Party Code: {$purchase->party_code}");
                }
            }

            return view('admin_panel.purchase.all_purchase', compact('Purchases'));
        } else {
            return redirect()->back();
        }
    }

    public function purchaseInvoice($id)
    {
        $purchase = Purchase::with('vendor')->findOrFail($id);

        $amounts = json_decode($purchase->amount, true) ?? [];
        $discounts = json_decode($purchase->discount, true) ?? [];

        $grossTotal = array_sum($amounts);
        $discountTotal = array_sum($discounts);
        $netTotal = $grossTotal - $discountTotal;

        $ledger = VendorLedger::where('vendor_id', $purchase->party_name)->first();

        $openingBalance = $ledger->opening_balance ?? 0;
        $previousBalance = $ledger->previous_balance ?? 0;
        $closingBalance = $ledger->closing_balance ?? 0;

        return view('admin_panel.purchase.invoice', compact(
            'purchase',
            'grossTotal',
            'discountTotal',
            'netTotal',
            'openingBalance',
            'previousBalance',
            'closingBalance'
        ));
    }

    public function purchaseedit($id)
    {

        if (Auth::id()) {
            $userId = Auth::id();

            $purchase = Purchase::findOrFail($id);
            $categories = Category::where('admin_or_user_id', $userId)->get();
            $Vendors = Vendor::where('admin_or_user_id', $userId)->get();

            return view('admin_panel.purchase.edit_purchase', compact('categories', 'Vendors', 'purchase'));
        } else {
            return redirect()->back();
        }
    }

    public function update_purchase(Request $request, $id)
    {
        $request->validate([
            'purchase_date' => 'required|date',
            'party_code' => 'required',
            'party_name' => 'required',
            'category' => 'required|array',
            'subcategory' => 'required|array',
            'item' => 'required|array',
            'rate' => 'required|array',
            'carton_qty' => 'required|array',
            'pcs' => 'required|array',
            'liter' => 'required|array',
            'gross_total' => 'required|array',
            'discount' => 'nullable|array',
            'amount' => 'required|array',
            'pcs_carton' => 'required|array',
            'grand_total' => 'required|numeric',
        ]);

        $userId = Auth::id();

        $purchase = Purchase::findOrFail($id);
        $oldItems = json_decode($purchase->item, true) ?? [];
        $oldAmounts = json_decode($purchase->amount, true) ?? [];

        $newItems = $request->item;
        $newAmounts = $request->amount;

        $diffAmount = 0;
        foreach ($newItems as $index => $itemName) {
            if (! in_array($itemName, $oldItems)) {
                $diffAmount += (float) $newAmounts[$index];
            }
        }
        foreach ($oldItems as $index => $itemName) {
            if (! in_array($itemName, $newItems)) {
                $diffAmount -= (float) $oldAmounts[$index];
            }
        }

        // Update Vendor Ledger
        $ledger = VendorLedger::where('vendor_id', $request->party_name)->first();

        if ($ledger) {
            $ledger->closing_balance = $ledger->closing_balance + $diffAmount;
            $ledger->previous_balance = $ledger->closing_balance;
            $ledger->admin_or_user_id = $userId;
            $ledger->updated_at = now();
            $ledger->save();
        } else {
            VendorLedger::create([
                'vendor_id' => $request->party_name,
                'admin_or_user_id' => $userId,
                'previous_balance' => 0,
                'closing_balance' => $request->grand_total,
                'updated_at' => now(),
            ]);
        }

        // Update the purchase data
        $purchaseData = [
            'admin_or_user_id' => $userId,
            'purchase_date' => $request->purchase_date,
            'party_code' => $request->party_code,
            'party_name' => $request->party_name,
            'category' => json_encode($request->category),
            'subcategory' => json_encode($request->subcategory),
            'item' => json_encode($request->item),
            'size' => json_encode($request->size),
            'rate' => json_encode($request->rate),
            'carton_qty' => json_encode($request->carton_qty),
            'pcs' => json_encode($request->pcs),
            'liter' => json_encode($request->liter),
            'gross_total' => json_encode($request->gross_total),
            'discount' => json_encode($request->discount ?? []),
            'amount' => json_encode($request->amount),
            'pcs_carton' => json_encode($request->pcs_carton),
            'grand_total' => $request->grand_total,
        ];

        $purchase->update($purchaseData);

        // Update Product Stock WITH SQ.FT TRACKING
        foreach ($request->item as $key => $item_name) {
            $category = $request->category[$key];
            $subcategory = $request->subcategory[$key];
            $carton_qty = $request->carton_qty[$key];
            $pcs = $request->pcs[$key];
            $rate = $request->rate[$key];

            $product = Product::where('item_name', $item_name)
                ->where('category', $category)
                ->where('sub_category', $subcategory)
                ->first();

            if ($product) {
                $previous_cartons = $product->carton_quantity;
                $pcs_in_carton = $product->pcs_in_carton;
                $previous_stock = $product->initial_stock;

                $new_carton_quantity = $previous_cartons + $carton_qty;
                $totalPiecesAdded = ($carton_qty * $pcs_in_carton) + $pcs;
                $new_initial_stock = $previous_stock + $totalPiecesAdded;

                $product->carton_quantity = $new_carton_quantity;
                $product->initial_stock = $new_initial_stock;

                // ✅ UPDATE SQUARE FOOTAGE IF PRODUCT HAS AREA
                if ($product->area && $product->area > 0) {
                    $addedSqft = $totalPiecesAdded * $product->area;
                    $product->total_sqft = ($product->total_sqft ?? 0) + $addedSqft;
                }

                $product->wholesale_price = $rate;
                $product->save();
            }
        }

        return redirect()->back()->with('success', 'Purchase updated successfully with Sq.ft tracking!');
    }
}
