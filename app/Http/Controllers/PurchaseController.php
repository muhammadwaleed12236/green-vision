<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\SubCategory;
use App\Models\Vendor;
use App\Models\VendorLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
        $query = Product::query()->select('id', 'item_name', 'size', 'pcs_in_carton', 'retail_price', 'product_mode', 'height', 'width', 'area');

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
        // basic validation for header fields
        $request->validate([
            'purchase_date' => 'required|date',
            'party_code' => 'required',
            'party_name' => 'required',
            'grand_total' => 'required|numeric',
        ]);

        $userId = Auth::id();
        $invoiceNo = Purchase::generateInvoiceNo();

        // RAW arrays from request (they may contain many blank rows)
        $item_names = $request->input('item_name', []);
        $rates = $request->input('rate', []);
        $product_modes = $request->input('product_mode', []);
        $measurements = $request->input('pcs', []);
        $discounts = $request->input('discount', []);
        $amounts = $request->input('amount', []);
        $gross_totals = $request->input('gross_total', []);
        $pcs_carton = $request->input('pcs_carton', []);

        // Build only filled rows array
        $rows = [];
        $totalRows = max(
            count($item_names), count($rates), count($product_modes),
            count($measurements), count($amounts), count($gross_totals), count($pcs_carton)
        );

        for ($i = 0; $i < $totalRows; $i++) {
            $name = trim($item_names[$i] ?? '');
            $rate = $rates[$i] ?? null;
            $carton = $product_modes[$i] ?? null;
            $pcs = $measurements[$i] ?? null;
            $amount = $amounts[$i] ?? null;
            $gross = $gross_totals[$i] ?? null;
            $pcs_in_cart = $pcs_carton[$i] ?? null;

            // Consider a row "filled" if it has item name OR rate OR carton qty OR pcs
            if ($name !== '' || (float) $rate > 0 || (float) $carton > 0 || (float) $pcs > 0) {
                $rows[] = [
                    'item_name' => $name,
                    'rate' => $rate,
                    'product_mode' => $carton,
                    'pcs' => $pcs,
                    'gross_total' => $gross,
                    'discount' => $discounts[$i] ?? 0,
                    'amount' => $amount,
                    'pcs_carton' => $pcs_in_cart,
                ];
            }
        }

        // If no rows filled -> error
        if (count($rows) === 0) {
            return back()
                ->withInput()
                ->withErrors(['items' => 'Please add at least one product/row before submitting.']);
        }

        // Validate each filled row (you can expand rules per field)
        $rowRules = [
            'item_name' => 'required|string',
            'rate' => 'nullable|numeric',
            'product_mode' => 'nullable',
            'pcs' => 'nullable|numeric',
            'amount' => 'required|numeric',
            'pcs_carton' => 'nullable|numeric',
        ];

        $rowErrors = [];
        foreach ($rows as $index => $r) {
            $validator = Validator::make($r, $rowRules);
            if ($validator->fails()) {
                $rowErrors["row_{$index}"] = $validator->errors()->all();
            }
        }

        if (! empty($rowErrors)) {
            // Flatten errors and return
            $flat = [];
            foreach ($rowErrors as $k => $errs) {
                foreach ($errs as $e) {
                    $flat[] = 'Row '.(intval(substr($k, 4)) + 1).': '.$e;
                }
            }

            return back()->withInput()->withErrors($flat);
        }

        // Prepare arrays for DB storage (only filled rows)
        $items_arr = array_column($rows, 'item_name');
        $rates_arr = array_map(function ($r) {
            return $r['rate'] ?? 0;
        }, $rows);
        $carton_arr = array_map(function ($r) {
            return $r['product_mode'] ?? 0;
        }, $rows);
        $measurement2 = array_map(function ($r) {
            return $r['measurement'] ?? 0;
        }, $rows);
        $gross_arr = array_map(function ($r) {
            return $r['gross_total'] ?? 0;
        }, $rows);
        $discount_arr = array_map(function ($r) {
            return $r['discount'] ?? 0;
        }, $rows);
        $amount_arr = array_map(function ($r) {
            return $r['amount'] ?? 0;
        }, $rows);
        $pcs_carton_arr = array_map(function ($r) {
            return $r['pcs_carton'] ?? 0;
        }, $rows);

        // Save purchase
        $purchaseData = [
            'admin_or_user_id' => $userId,
            'invoice_number' => $invoiceNo,
            'purchase_date' => $request->purchase_date,
            'party_code' => $request->party_code,
            'party_name' => $request->party_name,
            'item' => json_encode($items_arr),
            'size' => json_encode([]),
            'rate' => json_encode($rates_arr),
            'product_mode' => json_encode($carton_arr),
            'measurement' => json_encode($measurement2),
            'gross_total' => json_encode($gross_arr),
            'discount' => json_encode($discount_arr),
            'amount' => json_encode($amount_arr),
            'pcs_carton' => json_encode($pcs_carton_arr),
            'grand_total' => $request->grand_total,
        ];

        $purchase = Purchase::create($purchaseData);

        // Update product stock & price using item_name (if product exists)
        foreach ($rows as $r) {
            $itemName = $r['item_name'];
            if (! $itemName) {
                continue;
            }
            $product = Product::where('item_name', $itemName)->first();
            if (! $product) {
                continue;
            }

            $product_mode = (float) ($r['product_mode'] ?? 0);
            $pcs = (float) ($r['pcs'] ?? 0);
            $rate = (float) ($r['rate'] ?? 0);

            $pcs_in_carton = (float) ($product->pcs_in_carton ?? 0);
            $previous_cartons = (float) ($product->carton_quantity ?? 0);
            $previous_stock = (float) ($product->initial_stock ?? 0);

            $product->carton_quantity = $previous_cartons + $product_mode;
            $product->initial_stock = $previous_stock + ($product_mode * $pcs_in_carton) + $pcs;
            $product->wholesale_price = $rate;
            $product->save();
        }

        // Update vendor ledger
        $previousBalance = VendorLedger::where('vendor_id', $request->party_name)
            ->value('closing_balance') ?? 0;
        $newPreviousBalance = $request->grand_total;
        $newClosingBalance = $previousBalance + $request->grand_total;

        VendorLedger::updateOrCreate(
            ['vendor_id' => $request->party_name],
            [
                'vendor_id' => $request->party_name,
                'admin_or_user_id' => $userId,
                'previous_balance' => $newPreviousBalance,
                'closing_balance' => $newClosingBalance,
            ]
        );

        return redirect()->route('purchase.invoice', $purchase->id)->with('success', 'Purchase saved successfully and stock updated!');
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
        $purchase = Purchase::findOrFail($id);
        $purchase->gross_total_sum = array_sum(json_decode($purchase->amount));
        $purchase->discount_total_sum = array_sum(json_decode($purchase->discount));
        $purchase->grand_total = $purchase->gross_total_sum - $purchase->discount_total_sum;

        return view('admin_panel.purchase.invoice', compact('purchase'));
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
        // Validate the request data.  Use the same validation rules as store.
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

        // STEP 5: Update Vendor Ledger
        $ledger = VendorLedger::where('vendor_id', $request->party_name)->first();

        if ($ledger) {
            // Sirf difference add karo
            $ledger->closing_balance = $ledger->closing_balance + $diffAmount;
            $ledger->previous_balance = $ledger->closing_balance; // ya purana rakho agar required ho
            $ledger->admin_or_user_id = $userId;
            $ledger->updated_at = now();
            $ledger->save();
        } else {
            // Agar ledger pehli dafa ban raha hai
            VendorLedger::create([
                'vendor_id' => $request->party_name,
                'admin_or_user_id' => $userId,
                'previous_balance' => 0,
                'closing_balance' => $request->grand_total, // first entry
                'updated_at' => now(),
            ]);
        }

        // Update the purchase data.  Use the same structure as store.
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

        $purchase->update($purchaseData); // Use update() instead of create()

        // Step 2: Update Product Stock and Wholesale Price
        foreach ($request->item as $key => $item_name) {
            $category = $request->category[$key];
            $subcategory = $request->subcategory[$key];
            $carton_qty = $request->carton_qty[$key];
            $pcs = $request->pcs[$key];
            $rate = $request->rate[$key];

            // Find the product
            $product = Product::where('item_name', $item_name)
                ->where('category', $category)
                ->where('sub_category', $subcategory)
                ->first();

            if ($product) {
                // Pehle ka stock
                $previous_cartons = $product->carton_quantity;
                $pcs_in_carton = $product->pcs_in_carton;
                $previous_stock = $product->initial_stock;

                // Calculate new stock
                $new_carton_quantity = $previous_cartons + $carton_qty;
                $new_initial_stock = $previous_stock + ($carton_qty * $pcs_in_carton) + $pcs;

                // Update product stock and price
                $product->carton_quantity = $new_carton_quantity;
                $product->initial_stock = $new_initial_stock;
                $product->wholesale_price = $rate;
                $product->save();
            }
        }

        return redirect()->back()->with('success', 'Purchase updated successfully and stock updated!');
    }
}
