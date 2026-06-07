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
            ->pluck('sub_category_name', 'id'); // Fetch subcategory names with their IDs

        return response()->json($subcategories);
    }

    public function getItems(Request $request)
    {
        $q = $request->get('q', null);

        // base query - limit to current admin/user's products if you need (optional)
        $query = Product::query()->select('id', 'item_name', 'retail_price', 'wholesale_price', 'product_mode', 'height', 'width', 'area');
    
        if ($q === null || $q === '') {
            // return limited set (don't return everything)
            $items = $query->orderBy('item_name')->limit(200)->get();
        } else {
            // search (make sure to have index on item_name for performance)
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
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'purchase_date' => 'required|date',
            'party_code' => 'required',
            'party_name' => 'required',
            'grand_total' => 'required|numeric|min:0',
        ], [
            'party_name.required' => 'Vendor name is required',
            'party_code.required' => 'Vendor code is required',
        ]);

        // Check if request is AJAX
        if ($request->ajax() || $request->wantsJson()) {
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
        } else {
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
        }

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
        $itemErrors = [];

        foreach ($item_names as $i => $name) {
            $itemPcs = (int) ($pcs[$i] ?? 0);
            $itemRate = (int) ($rates[$i] ?? 0);

            // Only process rows with data
            if (trim($name) !== '' || $itemRate > 0 || $itemPcs > 0) {

                // Validate: If item has name, it must have pcs (feet)
                if (trim($name) !== '' && $itemPcs <= 0) {
                    $itemErrors[] = "Item \"{$name}\" requires Feet (pcs) value";
                }

                // Only add valid rows
                if (trim($name) !== '' && $itemPcs > 0) {
                    $rows[] = [
                        'item_name' => $name,
                        'rate' => $rates[$i] ?? 0,
                        'product_mode' => $cartons[$i] ?? 0,
                        'pcs' => $itemPcs,
                        'discount' => $discounts[$i] ?? 0,
                        'amount' => $amounts[$i] ?? 0,
                        'pcs_carton' => $pcs_carton[$i] ?? 0,
                    ];
                }
            }
        }

        // Return item-specific errors
        if (count($itemErrors) > 0) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['items' => $itemErrors]
                ], 422);
            }
            return back()->withErrors(['items' => implode(', ', $itemErrors)]);
        }

        if (count($rows) === 0) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['items' => ['At least one complete item is required (with Item Name, Rate, and Feet)']]
                ], 422);
            }
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
            'pcs' => json_encode(array_column($rows, 'pcs')),
            'discount' => json_encode(array_column($rows, 'discount')),
            'amount' => json_encode(array_column($rows, 'amount')),
            'pcs_carton' => json_encode(array_column($rows, 'pcs_carton')),
            'grand_total' => $request->grand_total,
        ]);

        // ================= UPDATE PRODUCT STOCK =================
        foreach ($rows as $row) {
            $product = Product::where('item_name', $row['item_name'])->first();
            if ($product) {
                $product->wholesale_price = $row['rate'];
                // Increment available stock by purchased pcs
                $product->initial_stock = ($product->initial_stock ?? 0) + ($row['pcs'] ?? 0);
                $product->save();
            }
        }

        // ================= VENDOR LEDGER (FINAL & CORRECT) =================
        $ledger = VendorLedger::where('vendor_id', $request->party_name)->first();

        $currentAmount = $request->grand_total;

        if ($ledger) {

            // ✅ previous = last closing
            $previousBalance = $ledger->closing_balance;

            $closingBalance = $previousBalance + $currentAmount;

            $ledger->update([
                'previous_balance' => $previousBalance,
                'closing_balance' => $closingBalance,
            ]);

        } else {

            // ✅ First time vendor
            $openingBalance = 0; // agar vendor table me opening ho to wahan se lao
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

        // ================= RESPONSE =================
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Purchase saved & ledger updated successfully!',
                'redirect' => route('purchase.invoice', $purchase->id)
            ]);
        }

        return redirect()
            ->route('purchase.invoice', $purchase->id)
            ->with('success', 'Purchase saved & ledger updated successfully');
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
        // dd($amounts);
        $discounts = json_decode($purchase->discount, true) ?? [];

        $grossTotal = array_sum($amounts);
        $discountTotal = array_sum($discounts);
        $netTotal = $grossTotal ;
        // dd($netTotal);

        // ✅ Ledger se DIRECT uthao
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
        // ================= VALIDATION =================
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'purchase_date' => 'required|date',
            'party_code' => 'required',
            'party_name' => 'required',
            'grand_total' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $userId = Auth::id();
        $purchase = Purchase::findOrFail($id);

        // ================= GET OLD ITEMS FOR STOCK ADJUSTMENT =================
        $oldItems = json_decode($purchase->item, true) ?? [];
        $oldPcs = json_decode($purchase->pcs, true) ?? [];

        // ================= ITEMS (FILTER EMPTY ROWS) =================
        $item_names = $request->item_name ?? [];
        $rates = $request->rate ?? [];
        $productModes = $request->product_mode ?? [];
        $pcs = $request->pcs ?? [];
        $discounts = $request->discount ?? [];
        $amounts = $request->amount ?? [];

        $rows = [];

        foreach ($item_names as $i => $name) {
            $itemPcs = (int) ($pcs[$i] ?? 0);

            if (trim($name) !== '' && $itemPcs > 0) {
                $rows[] = [
                    'item_name' => $name,
                    'rate' => $rates[$i] ?? 0,
                    'product_mode' => $productModes[$i] ?? '',
                    'pcs' => $itemPcs,
                    'discount' => $discounts[$i] ?? 0,
                    'amount' => $amounts[$i] ?? 0,
                ];
            }
        }

        if (count($rows) === 0) {
            return back()->withErrors(['items' => 'At least one complete item is required'])->withInput();
        }

        // ================= CALCULATE LEDGER DIFFERENCE =================
        $oldGrandTotal = $purchase->grand_total;
        $newGrandTotal = $request->grand_total;
        $diffAmount = $newGrandTotal - $oldGrandTotal;

        // ================= UPDATE VENDOR LEDGER =================
        $ledger = VendorLedger::where('vendor_id', $request->party_name)->first();

        if ($ledger) {
            $ledger->closing_balance = $ledger->closing_balance + $diffAmount;
            $ledger->admin_or_user_id = $userId;
            $ledger->updated_at = now();
            $ledger->save();
        } else {
            VendorLedger::create([
                'vendor_id' => $request->party_name,
                'admin_or_user_id' => $userId,
                'previous_balance' => 0,
                'closing_balance' => $newGrandTotal,
                'updated_at' => now(),
            ]);
        }

        // ================= UPDATE PURCHASE =================
        $purchase->update([
            'admin_or_user_id' => $userId,
            'purchase_date' => $request->purchase_date,
            'party_code' => $request->party_code,
            'party_name' => $request->party_name,
            'item' => json_encode(array_column($rows, 'item_name')),
            'rate' => json_encode(array_column($rows, 'rate')),
            'product_mode' => json_encode(array_column($rows, 'product_mode')),
            'pcs' => json_encode(array_column($rows, 'pcs')),
            'discount' => json_encode(array_column($rows, 'discount')),
            'amount' => json_encode(array_column($rows, 'amount')),
            'grand_total' => $newGrandTotal,
        ]);

        // ================= REVERSE OLD STOCK & UPDATE WITH NEW STOCK =================
        // First, reverse old stock
        foreach ($oldItems as $i => $oldItemName) {
            $oldQty = (int) ($oldPcs[$i] ?? 0);
            if ($oldItemName && $oldQty > 0) {
                $product = Product::where('item_name', $oldItemName)->first();
                if ($product) {
                    // Subtract old stock
                    $product->initial_stock = max(0, ($product->initial_stock ?? 0) - $oldQty);
                    $product->save();
                }
            }
        }

        // Then, add new stock
        foreach ($rows as $row) {
            $product = Product::where('item_name', $row['item_name'])->first();
            if ($product) {
                $product->wholesale_price = $row['rate'];
                // Add new stock
                $product->initial_stock = ($product->initial_stock ?? 0) + ($row['pcs'] ?? 0);
                $product->save();
            }
        }

        // ================= REDIRECT =================
        return redirect()->route('all-Purchases')->with('success', 'Purchase updated successfully!');
    }

    public function delete_purchase($id)
    {
        $userId = Auth::id();
        $purchase = Purchase::findOrFail($id);

        // ================= REVERSE VENDOR LEDGER =================
        $ledger = VendorLedger::where('vendor_id', $purchase->party_name)->first();
        if ($ledger) {
            $ledger->closing_balance = $ledger->closing_balance - $purchase->grand_total;
            $ledger->admin_or_user_id = $userId;
            $ledger->updated_at = now();
            $ledger->save();
        }

        // ================= REVERSE STOCK =================
        $oldItems = json_decode($purchase->item, true) ?? [];
        $oldPcs = json_decode($purchase->pcs, true) ?? [];

        foreach ($oldItems as $i => $itemName) {
            $qty = (int) ($oldPcs[$i] ?? 0);
            if ($itemName && $qty > 0) {
                $product = Product::where('item_name', $itemName)->first();
                if ($product) {
                    // Subtract stock
                    $product->initial_stock = max(0, ($product->initial_stock ?? 0) - $qty);
                    $product->save();
                }
            }
        }

        // ================= DELETE PURCHASE =================
        $purchase->delete();

        // ================= REDIRECT =================
        return redirect()->route('all-Purchases')->with('success', 'Purchase deleted successfully!');
    }
}
