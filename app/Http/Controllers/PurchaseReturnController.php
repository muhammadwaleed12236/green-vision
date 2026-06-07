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

    public function showReturnForm($id)
    {
        $purchase = Purchase::with('vendor')->findOrFail($id);

        // Decode JSON fields
        $purchase->item = json_decode($purchase->item, true);
        $purchase->rate = json_decode($purchase->rate, true);
        $purchase->product_mode = json_decode($purchase->product_mode, true);
        $purchase->pcs = json_decode($purchase->pcs, true);
        $purchase->discount = json_decode($purchase->discount, true);
        $purchase->amount = json_decode($purchase->amount, true);
        $purchase->pcs_carton = json_decode($purchase->pcs_carton, true);

        return view('admin_panel.purchase_return.purcahse_return', compact('purchase'));
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

        $rows = [];
        $totalReturnAmount = 0;

        // Filter valid rows with data
        foreach ($items as $i => $itemName) {
            $qty = (float)($returnQtys[$i] ?? 0);
            $amount = (float)($returnAmounts[$i] ?? 0);

            // Only process rows with valid data
            if (trim($itemName) !== '' && $qty > 0 && $amount > 0) {
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
        ]);

        // Step 2: Update return status in purchase
        Purchase::where('id', $purchaseId)->update(['return_status' => 1]);

        // Step 3: No stock tracking for simplified products
        // Stock management removed as per simplified product structure

        // Step 4: Update closing_balance of vendor
        $vendorId = $request->party_name; // vendor_id
        $vendorLedger = VendorLedger::where('vendor_id', $vendorId)->first();

        if ($vendorLedger) {
            $vendorLedger->closing_balance = $vendorLedger->closing_balance - $totalReturnAmount;
            $vendorLedger->save();
        }

        return redirect()->route('all-Purchases')->with('success', 'Purchase return processed successfully.');
        // return redirect()->back()->with('success', 'Purchase return saved, stock updated, and vendor ledger adjusted successfully.');
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
