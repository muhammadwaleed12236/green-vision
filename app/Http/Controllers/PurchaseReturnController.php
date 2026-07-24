<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\VendorLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseReturnController extends Controller
{
    public function showReturnForm($id = null)
    {
        $purchase = null;
        if ($id) {
            $purchase = Purchase::with('vendor')->findOrFail($id);
        }
        return view('admin_panel.purchase_return.purcahse_return', compact('purchase'));
    }

    public function getPurchaseInvoices(Request $request)
    {
        $userId = Auth::id();
        $date = $request->get('date');
        $search = trim($request->get('search', ''));

        $query = Purchase::with('vendor')->where('admin_or_user_id', $userId);

        if ($date) {
            $query->whereDate('purchase_date', $date);
        }

        if ($search !== '') {
            $query->where('invoice_number', 'like', '%' . $search . '%');
        }

        $purchases = $query->orderBy('id', 'desc')->get();

        $result = $purchases->map(function ($purchase) {
            return [
                'id' => $purchase->id,
                'invoice_number' => $purchase->invoice_number,
                'vendor_name' => $purchase->vendor->Party_name ?? 'N/A',
                'party_name' => $purchase->party_name, // vendor_id
                'label' => $purchase->invoice_number . ' (' . ($purchase->vendor->Party_name ?? 'N/A') . ')',
            ];
        });

        return response()->json($result);
    }

    private function getReturnedQuantities($purchaseId)
    {
        $returns = PurchaseReturn::where('purchase_id', $purchaseId)->get();
        $returnedTotals = [];

        foreach ($returns as $ret) {
            $items = json_decode($ret->item ?? '[]', true) ?: [];
            $qtys = json_decode($ret->return_qty ?? '[]', true) ?: [];
            foreach ($items as $idx => $itemName) {
                $qty = (float)($qtys[$idx] ?? 0);
                if (isset($returnedTotals[$itemName])) {
                    $returnedTotals[$itemName] += $qty;
                } else {
                    $returnedTotals[$itemName] = $qty;
                }
            }
        }
        return $returnedTotals;
    }

    public function fetchPurchaseDetails(Request $request)
    {
        $id = $request->input('id');
        $purchase = Purchase::with('vendor')->find($id);

        if (!$purchase) {
            return response()->json(['success' => false, 'message' => 'Purchase invoice not found.']);
        }

        $returnedQtys = $this->getReturnedQuantities($purchase->id);

        $items = is_string($purchase->item) ? (json_decode($purchase->item, true) ?? []) : ($purchase->item ?? []);
        $rates = is_string($purchase->rate) ? (json_decode($purchase->rate, true) ?? []) : ($purchase->rate ?? []);
        $pcs = is_string($purchase->pcs) ? (json_decode($purchase->pcs, true) ?? []) : ($purchase->pcs ?? []);
        $discounts = is_string($purchase->discount) ? (json_decode($purchase->discount, true) ?? []) : ($purchase->discount ?? []);

        $lines = [];
        foreach ($items as $index => $item) {
            $purchasedQty = (float)($pcs[$index] ?? 0);
            $alreadyReturned = (float)($returnedQtys[$item] ?? 0);
            $availableQty = max(0, $purchasedQty - $alreadyReturned);

            $lines[] = [
                'index' => $index,
                'item' => $item,
                'rate' => (float)($rates[$index] ?? 0),
                'discount' => (float)($discounts[$index] ?? 0),
                'purchased_qty' => $purchasedQty,
                'already_returned' => $alreadyReturned,
                'available_qty' => $availableQty,
            ];
        }

        return response()->json([
            'success' => true,
            'purchase' => [
                'id' => $purchase->id,
                'invoice_number' => $purchase->invoice_number,
                'purchase_date' => $purchase->purchase_date,
                'party_name' => $purchase->party_name,
                'vendor_name' => $purchase->vendor->Party_name ?? 'N/A',
                'grand_total' => $purchase->grand_total,
            ],
            'items' => $lines
        ]);
    }

    public function store(Request $request)
    {
        $purchaseId = $request->purchase_id;
        $userId = Auth::id();

        // ================= FILTER & VALIDATE ITEMS (LIKE PURCHASE) =================
        $items = $request->item ?? [];
        $rates = $request->rate ?? [];
        $returnQtys = $request->return_qty ?? [];
        $discounts = $request->discount ?? [];
        $returnAmounts = $request->return_amount ?? [];

        // Server-side validation of return quantities against available
        $returnedQtys = $this->getReturnedQuantities($purchaseId);
        $purchase = Purchase::findOrFail($purchaseId);
        $purchaseItems = is_string($purchase->item) ? (json_decode($purchase->item, true) ?? []) : ($purchase->item ?? []);
        $purchasePcs = is_string($purchase->pcs) ? (json_decode($purchase->pcs, true) ?? []) : ($purchase->pcs ?? []);

        $rows = [];
        $totalReturnAmount = 0;

        // Filter valid rows with data
        foreach ($items as $i => $itemName) {
            $qty = (float)($returnQtys[$i] ?? 0);
            $amount = (float)($returnAmounts[$i] ?? 0);

            // Only process rows with valid data
            if (trim($itemName) !== '' && $qty > 0 && $amount > 0) {
                // Find matching item in purchase for stock validation
                $foundIndex = array_search($itemName, $purchaseItems);
                if ($foundIndex === false) {
                    return redirect()->back()->with('error', "Item '$itemName' not found on the original purchase invoice.");
                }

                $purchasedQty = (float)($purchasePcs[$foundIndex] ?? 0);
                $alreadyReturned = (float)($returnedQtys[$itemName] ?? 0);
                $availableQty = $purchasedQty - $alreadyReturned;

                if ($qty > $availableQty) {
                    return redirect()->back()->with('error', "Return quantity for '$itemName' ($qty) exceeds available quantity ($availableQty).");
                }

                $rows[] = [
                    'item' => $itemName,
                    'rate' => $rates[$i] ?? 0,
                    'return_qty' => $qty,
                    'discount' => $discounts[$i] ?? 0,
                    'return_amount' => $amount,
                ];
                $totalReturnAmount += $amount;
            }
        }

        // Validate: At least one item must be returned
        if (count($rows) === 0) {
            return redirect()->back()->with('error', 'At least one item with return quantity is required.');
        }

        // Step 1: Save the return record
        PurchaseReturn::create([
            'admin_or_user_id' => $userId,
            'purchase_id' => $purchaseId,
            'party_name' => $request->party_name,
            'return_date' => $request->return_date,
            'item' => json_encode(array_column($rows, 'item')),
            'rate' => json_encode(array_column($rows, 'rate')),
            'return_qty' => json_encode(array_column($rows, 'return_qty')),
            'discount' => json_encode(array_column($rows, 'discount')),
            'return_amount' => json_encode(array_column($rows, 'return_amount')),
            'total_return_amount' => $totalReturnAmount,
            'return_items' => implode(', ', array_column($rows, 'item')),
            'reason' => $request->reason,
            'notes' => $request->notes,
        ]);

        // Step 2: Update return status in purchase
        Purchase::where('id', $purchaseId)->update(['return_status' => 1]);

        // Step 3: Update closing_balance of vendor
        $vendorId = $request->party_name; // vendor_id
        $vendorLedger = VendorLedger::where('vendor_id', $vendorId)->first();

        if ($vendorLedger) {
            $vendorLedger->closing_balance = $vendorLedger->closing_balance - $totalReturnAmount;
            $vendorLedger->save();
        }

        // Step 4: Update product stock
        foreach ($rows as $row) {
            $product = Product::where('item_name', $row['item'])->first();
            if ($product) {
                $product->initial_stock = max(0, ($product->initial_stock ?? 0) - $row['return_qty']);
                $product->save();
            }
        }

        return redirect()->route('all-purchase-return')->with('success', 'Purchase return processed successfully.');
    }

    public function all_purchase_return()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Purchases = PurchaseReturn::with('purchase')->where('admin_or_user_id', $userId)->get();
            return view('admin_panel.purchase_return.all_purchase_return', compact('Purchases'));
        } else {
            return redirect()->back();
        }
    }
}
