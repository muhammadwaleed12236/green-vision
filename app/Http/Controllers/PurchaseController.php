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
        $query = Product::query()->select('id', 'item_name', 'retail_price', 'wholesale_price', 'unit');
    
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
        try {
            // ================= VALIDATION =================
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'purchase_date' => 'required|date',
                'party_code' => 'required',
                'party_name' => 'required|numeric',
                'grand_total' => 'required|numeric|min:0',
            ], [
                'party_name.required' => 'Vendor name is required',
                'party_name.numeric' => 'Invalid vendor selected',
                'party_code.required' => 'Vendor code is required',
            ]);

            if ($validator->fails()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'errors' => $validator->errors()
                    ], 422);
                }
                return back()->withErrors($validator)->withInput();
            }

            // ================= VERIFY VENDOR EXISTS =================
            $vendorId = (int) $request->party_name;
            $vendor = Vendor::findOrFail($vendorId);

            $userId = Auth::id();
            $invoiceNo = Purchase::generateInvoiceNo();

            // ================= ITEMS (FILTER EMPTY ROWS) =================
            $item_names = $request->item_name ?? [];
            $rates = $request->rate ?? [];
            $cartons = $request->unit ?? [];
            $pcs = $request->pcs ?? [];
            $discounts = $request->discount ?? [];
            $amounts = $request->amount ?? [];
            $pcs_carton = $request->pcs_carton ?? [];

            $rows = [];
            $itemErrors = [];

            foreach ($item_names as $i => $name) {
                $itemPcs = (int) ($pcs[$i] ?? 0);
                $itemRate = (float) ($rates[$i] ?? 0);

                // Only process rows with data
                if (trim($name) !== '' || $itemRate > 0 || $itemPcs > 0) {
                    // Only add valid rows
                    if (trim($name) !== '') {
                        $rows[] = [
                            'item_name' => trim($name),
                            'rate' => $itemRate,
                            'product_mode' => $cartons[$i] ?? '',
                            'pcs' => $itemPcs,
                            'discount' => (float) ($discounts[$i] ?? 0),
                            'amount' => (float) ($amounts[$i] ?? 0),
                            'pcs_carton' => (int) ($pcs_carton[$i] ?? 0),
                        ];
                    }
                }
            }

            if (count($rows) === 0) {
                $errorMsg = 'At least one complete item is required (with Item Name)';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'errors' => ['items' => [$errorMsg]]
                    ], 422);
                }
                return back()->withErrors(['items' => $errorMsg])->withInput();
            }

            // ================= SAVE PURCHASE WITH VENDOR_ID =================
            $purchase = Purchase::create([
                'admin_or_user_id' => $userId,
                'vendor_id' => $vendorId,
                'invoice_number' => $invoiceNo,
                'purchase_date' => $request->purchase_date,
                'party_code' => $vendor->Party_code,
                'party_name' => $vendor->id,
                'item' => json_encode(array_column($rows, 'item_name')),
                'rate' => json_encode(array_column($rows, 'rate')),
                'product_mode' => json_encode(array_column($rows, 'product_mode')),
                'pcs' => json_encode(array_column($rows, 'pcs')),
                'discount' => json_encode(array_column($rows, 'discount')),
                'amount' => json_encode(array_column($rows, 'amount')),
                'pcs_carton' => json_encode(array_column($rows, 'pcs_carton')),
                'grand_total' => (float) $request->grand_total,
            ]);

            // ================= UPDATE PRODUCT STOCK =================
            foreach ($rows as $row) {
                $product = Product::where('item_name', $row['item_name'])->first();
                if ($product) {
                    $product->wholesale_price = $row['rate'];
                    $product->initial_stock = ($product->initial_stock ?? 0) + $row['pcs'];
                    $product->save();
                }
            }

            // ================= UPDATE VENDOR LEDGER =================
            $ledger = VendorLedger::where('vendor_id', $vendorId)->latest()->first();

            $currentAmount = (float) $request->grand_total;
            $openingBalance = $vendor->opening_balance ?? 0;

            if ($ledger) {
                $previousBalance = $ledger->closing_balance;
                $closingBalance = $previousBalance + $currentAmount;

                $ledger->update([
                    'previous_balance' => $previousBalance,
                    'closing_balance' => $closingBalance,
                ]);
            } else {
                $previousBalance = $openingBalance;
                $closingBalance = $openingBalance + $currentAmount;

                VendorLedger::create([
                    'admin_or_user_id' => $userId,
                    'vendor_id' => $vendorId,
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

        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Purchase Store - Database Error: ' . $e->getMessage(), [
                'request_data' => $request->except(['*password*']),
                'trace' => $e->getTraceAsString()
            ]);
            
            $message = 'Database error occurred. Please try again.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'error' => config('app.debug') ? $e->getMessage() : 'Database error'
                ], 500);
            }
            return back()->withErrors(['error' => $message])->withInput();

        } catch (\Exception $e) {
            \Log::error('Purchase Store - General Error: ' . $e->getMessage(), [
                'request_data' => $request->except(['*password*']),
                'trace' => $e->getTraceAsString()
            ]);
            
            $message = 'An unexpected error occurred. Please try again.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'error' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }
            return back()->withErrors(['error' => $message])->withInput();
        }
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
        try {
            $purchase = Purchase::with('vendor')->findOrFail($id);

            $amounts = $purchase->amount ?? [];
            $discounts = $purchase->discount ?? [];

            $grossTotal = array_sum($amounts);
            $discountTotal = array_sum($discounts);
            $netTotal = $grossTotal - $discountTotal;

            // Get ledger for this purchase
            $ledger = VendorLedger::where('vendor_id', $purchase->party_name)
                ->latest()
                ->first();

            if (!$ledger) {
                \Log::warning("No vendor ledger found for purchase {$id}", [
                    'vendor_id' => $purchase->party_name
                ]);
            }

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

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Purchase not found: ' . $id);
            abort(404, 'Purchase not found');
        } catch (\Exception $e) {
            \Log::error('Error loading purchase invoice: ' . $e->getMessage(), [
                'purchase_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            abort(500, 'Error loading purchase invoice');
        }
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
        try {
            // ================= VALIDATION =================
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'purchase_date' => 'required|date',
                'party_code' => 'required',
                'party_name' => 'required|numeric',
                'grand_total' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $userId = Auth::id();
            $purchase = Purchase::findOrFail($id);
            $vendorId = (int) $request->party_name;

            // Verify vendor exists
            $vendor = Vendor::findOrFail($vendorId);

            // ================= GET OLD ITEMS FOR STOCK ADJUSTMENT =================
            $oldItems = $purchase->item ?? [];
            $oldPcs = $purchase->pcs ?? [];

            // ================= ITEMS (FILTER EMPTY ROWS) =================
            $item_names = $request->item_name ?? [];
            $rates = $request->rate ?? [];
            $productModes = $request->unit ?? [];
            $pcs = $request->pcs ?? [];
            $discounts = $request->discount ?? [];
            $amounts = $request->amount ?? [];

            $rows = [];

            foreach ($item_names as $i => $name) {
                $itemPcs = (int) ($pcs[$i] ?? 0);
                $itemRate = (float) ($rates[$i] ?? 0);

                if (trim($name) !== '') {
                    $rows[] = [
                        'item_name' => trim($name),
                        'rate' => $itemRate,
                        'product_mode' => $productModes[$i] ?? '',
                        'pcs' => $itemPcs,
                        'discount' => (float) ($discounts[$i] ?? 0),
                        'amount' => (float) ($amounts[$i] ?? 0),
                    ];
                }
            }

            if (count($rows) === 0) {
                return back()->withErrors(['items' => 'At least one complete item is required'])->withInput();
            }

            // ================= CALCULATE LEDGER DIFFERENCE =================
            $oldGrandTotal = (float) $purchase->grand_total;
            $newGrandTotal = (float) $request->grand_total;
            $diffAmount = $newGrandTotal - $oldGrandTotal;

            // ================= UPDATE VENDOR LEDGER =================
            $ledger = VendorLedger::where('vendor_id', $vendorId)->latest()->first();

            if ($ledger) {
                $ledger->update([
                    'previous_balance' => $ledger->closing_balance,
                    'closing_balance' => $ledger->closing_balance + $diffAmount,
                ]);
            } else {
                // Shouldn't happen for existing purchase, but handle it
                VendorLedger::create([
                    'vendor_id' => $vendorId,
                    'admin_or_user_id' => $userId,
                    'opening_balance' => 0,
                    'previous_balance' => 0,
                    'closing_balance' => $newGrandTotal,
                ]);
            }

            // ================= UPDATE PURCHASE =================
            $purchase->update([
                'admin_or_user_id' => $userId,
                'vendor_id' => $vendorId,
                'purchase_date' => $request->purchase_date,
                'party_code' => $vendor->Party_code,
                'party_name' => $vendorId,
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
                    $product->initial_stock = ($product->initial_stock ?? 0) + $row['pcs'];
                    $product->save();
                }
            }

            return redirect()
                ->route('all-Purchases')
                ->with('success', 'Purchase updated successfully!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Update Purchase - Model not found: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Purchase or Vendor not found'])->withInput();

        } catch (\Exception $e) {
            \Log::error('Update Purchase - Error: ' . $e->getMessage(), [
                'purchase_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Failed to update purchase. Please try again.'])->withInput();
        }
    }

    public function delete_purchase($id)
    {
        try {
            $userId = Auth::id();
            $purchase = Purchase::findOrFail($id);
            $vendorId = (int) $purchase->party_name;

            // ================= REVERSE VENDOR LEDGER =================
            $ledger = VendorLedger::where('vendor_id', $vendorId)->latest()->first();
            
            if ($ledger) {
                $ledger->update([
                    'closing_balance' => $ledger->closing_balance - $purchase->grand_total,
                ]);
            }

            // ================= REVERSE STOCK =================
            $oldItems = $purchase->item ?? [];
            $oldPcs = $purchase->pcs ?? [];

            foreach ($oldItems as $i => $itemName) {
                $qty = (int) ($oldPcs[$i] ?? 0);
                if ($itemName && $qty > 0) {
                    $product = Product::where('item_name', $itemName)->first();
                    if ($product) {
                        $product->initial_stock = max(0, ($product->initial_stock ?? 0) - $qty);
                        $product->save();
                    }
                }
            }

            // ================= SOFT DELETE PURCHASE =================
            $purchase->delete();

            \Log::info('Purchase deleted', [
                'purchase_id' => $id,
                'vendor_id' => $vendorId,
                'amount' => $purchase->grand_total
            ]);

            return redirect()
                ->route('all-Purchases')
                ->with('success', 'Purchase deleted successfully!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Delete Purchase - Purchase not found: ' . $id);
            return back()->withErrors(['error' => 'Purchase not found'])->withInput();

        } catch (\Exception $e) {
            \Log::error('Delete Purchase - Error: ' . $e->getMessage(), [
                'purchase_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Failed to delete purchase. Please try again.'])->withInput();
        }
    }
}
