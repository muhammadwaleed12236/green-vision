<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\LocalSale;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Vendor;
use App\Models\VendorLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use App\Traits\AutoJournalVoucher;

class WizardController extends Controller
{
    use AutoJournalVoucher;
    public function index()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $vendors = Vendor::where('admin_or_user_id', $userId)->get();
            $customers = Customer::where('admin_or_user_id', $userId)->get();
            $categories = Category::where('admin_or_user_id', $userId)->get();
            $products = Product::where('admin_or_user_id', $userId)->get();
            $accounts = Account::where('status', true)->get();
            
            return view('admin_panel.wizard.index', compact('vendors', 'customers', 'categories', 'products', 'accounts'));
        } else {
            return redirect()->back();
        }
    }

    public function getSales()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            // Fetch sales that are booking, estimate, or pending
            $sales = LocalSale::where('admin_or_user_id', $userId)
                ->where(function ($query) {
                    $query->whereIn('sale_type', ['booking', 'estimate'])
                          ->orWhere('job_status', 'pending');
                })
                ->orderBy('id', 'desc')
                ->get();
                
            return response()->json($sales);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function getSaleDetails($id)
    {
        if (Auth::id()) {
            $sale = LocalSale::with('customer')->findOrFail($id);
            
            // Format products for easier use in frontend
            $items = json_decode($sale->item, true) ?? [];
            $rates = json_decode($sale->rate, true) ?? [];
            $qtys = json_decode($sale->qty, true) ?? [];
            $units = json_decode($sale->unit, true) ?? [];
            
            $products = [];
            foreach ($items as $index => $item) {
                $product = \App\Models\Product::where('item_name', $item)->first();
                $purchaseRate = $product ? $product->wholesale_price : 0;

                $products[] = [
                    'item_name' => $item,
                    'rate' => $rates[$index] ?? 0,
                    'purchase_rate' => $purchaseRate,
                    'qty' => $qtys[$index] ?? 0,
                    'unit' => $units[$index] ?? '',
                ];
            }

            // Fetch customer previous balance if it's a customer
            $previousBalance = 0;
            if ($sale->party_type === 'customer' && $sale->customer_id) {
                $ledger = CustomerLedger::where('customer_id', $sale->customer_id)->latest()->first();
                if ($ledger) {
                    $previousBalance = $ledger->closing_balance;
                } else {
                    $customer = Customer::find($sale->customer_id);
                    $previousBalance = $customer ? ($customer->opening_balance ?? 0) : 0;
                }
            }
            
            return response()->json([
                'sale' => $sale,
                'products' => $products,
                'previous_balance' => $previousBalance
            ]);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function createPurchase(Request $request)
    {
        if (!Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            DB::beginTransaction();

            $userId = Auth::id();
            $purchaseDate = date('Y-m-d');
            $products = $request->products ?? []; // Expecting array of objects from frontend
            $vendorPaymentsInput = $request->vendor_payments ?? []; // Keyed by vendor_id or array of objects

            if (count($products) === 0) {
                return response()->json(['success' => false, 'message' => 'No products provided for purchase']);
            }

            // Normalize vendor payments input
            $vendorPaymentsMap = [];
            if (is_array($vendorPaymentsInput)) {
                foreach ($vendorPaymentsInput as $key => $val) {
                    if (isset($val['vendor_id'])) {
                        $vendorPaymentsMap[(int)$val['vendor_id']] = $val;
                    } else {
                        $vendorPaymentsMap[(int)$key] = $val;
                    }
                }
            }

            // Group products by vendor_id
            $vendorGroups = [];

            foreach ($products as $prod) {
                $vendorId = (int) ($prod['vendor_id'] ?? $request->vendor_id ?? 0);
                if (!$vendorId) {
                    return response()->json(['success' => false, 'message' => 'Please select a vendor for product: ' . ($prod['item_name'] ?? 'Item')]);
                }
                $rate = floatval($prod['rate'] ?? 0);
                $pcs = floatval($prod['pcs'] ?? 0);
                $amount = $rate * $pcs;

                if (!isset($vendorGroups[$vendorId])) {
                    $vendorGroups[$vendorId] = [
                        'vendor' => Vendor::findOrFail($vendorId),
                        'products' => [],
                        'total' => 0,
                    ];
                }
                $vendorGroups[$vendorId]['products'][] = $prod;
                $vendorGroups[$vendorId]['total'] += $amount;
            }

            $createdPurchaseIds = [];

            foreach ($vendorGroups as $vendorId => $group) {
                $vendor = $group['vendor'];
                $vendorProducts = $group['products'];
                $vendorTotal = $group['total'];

                // Read payment specifically for this vendor
                $vPayInfo = $vendorPaymentsMap[$vendorId] ?? [];
                $vendorPaymentAmount = floatval($vPayInfo['payment_amount'] ?? 0);
                $vendorAccountId = $vPayInfo['account_id'] ?? null;

                if ($vendorPaymentAmount > 0 && !$vendorAccountId) {
                    return response()->json([
                        'success' => false,
                        'message' => "Please select a payment account for {$vendor->Party_name}"
                    ]);
                }

                $item_names = [];
                $rates = [];
                $product_modes = [];
                $pcs = [];
                $discounts = [];
                $amounts = [];
                $pcs_cartons = [];

                foreach ($vendorProducts as $prod) {
                    $item_names[] = $prod['item_name'];
                    $rates[] = $prod['rate'];
                    $product_modes[] = $prod['unit'] ?? '';
                    $pcs[] = $prod['pcs'] ?? 0;
                    $discounts[] = 0;
                    $amt = (float)$prod['rate'] * (float)($prod['pcs'] ?? 0);
                    $amounts[] = $amt;
                    $pcs_cartons[] = 0;
                }

                $invoiceNo = Purchase::generateInvoiceNo();
                $purchase = Purchase::create([
                    'admin_or_user_id' => $userId,
                    'vendor_id' => $vendorId,
                    'invoice_number' => $invoiceNo,
                    'purchase_date' => $purchaseDate,
                    'party_code' => $vendor->Party_code,
                    'party_name' => $vendor->id,
                    'item' => json_encode($item_names),
                    'rate' => json_encode($rates),
                    'product_mode' => json_encode($product_modes),
                    'pcs' => json_encode($pcs),
                    'discount' => json_encode($discounts),
                    'amount' => json_encode($amounts),
                    'pcs_carton' => json_encode($pcs_cartons),
                    'grand_total' => $vendorTotal,
                ]);
                $createdPurchaseIds[] = $purchase->id;

                // Update Vendor Ledger for THIS specific vendor
                $ledger = VendorLedger::where('vendor_id', $vendorId)->latest()->first();
                $openingBalance = $vendor->opening_balance ?? 0;

                if ($ledger) {
                    $previousBalance = $ledger->closing_balance;
                    $closingBalance = $previousBalance + $vendorTotal - $vendorPaymentAmount;

                    $ledger->update([
                        'previous_balance' => $previousBalance,
                        'closing_balance' => $closingBalance,
                    ]);
                } else {
                    VendorLedger::create([
                        'admin_or_user_id' => $userId,
                        'vendor_id' => $vendorId,
                        'opening_balance' => $openingBalance,
                        'previous_balance' => $openingBalance,
                        'closing_balance' => $openingBalance + $vendorTotal - $vendorPaymentAmount,
                    ]);
                }

                // Record payment entry & Journal Voucher for this vendor if payment made
                if ($vendorPaymentAmount > 0) {
                    \App\Models\VendorPayment::create([
                        'admin_or_user_id' => $userId,
                        'vendor_id' => $vendorId,
                        'amount' => $vendorPaymentAmount,
                        'payment_date' => $purchaseDate,
                        'remarks' => 'Wizard Payment for Purchase ID: ' . $purchase->id,
                    ]);

                    // Create Journal Voucher (payment) for this vendor payment
                    $voucherNo = \App\Models\JournalVoucher::generateVoucherNo('payment');
                    \App\Models\JournalVoucher::create([
                        'admin_or_user_id' => $userId,
                        'account_id' => $vendorAccountId,
                        'voucher_no' => $voucherNo,
                        'voucher_date' => $purchaseDate,
                        'voucher_type' => 'payment',
                        'party_type' => 'vendor',
                        'party_id' => $vendorId,
                        'party_name' => $vendor->Party_name,
                        'account_head' => 'Purchase',
                        'debit_amount' => $vendorPaymentAmount,
                        'credit_amount' => 0,
                        'payment_method' => 'cash',
                        'reference_type' => 'purchase',
                        'reference_id' => $purchase->id,
                        'narration' => 'Wizard Purchase - ' . $purchase->invoice_number . ' (' . $vendor->Party_name . ')',
                        'status' => 'approved',
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => count($createdPurchaseIds) > 1 
                    ? count($createdPurchaseIds) . ' purchase bills created and ledgers updated successfully for respective vendors!'
                    : 'Purchase created and vendor ledger updated successfully!',
                'purchase_id' => $createdPurchaseIds[0] ?? null
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Wizard Purchase Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to create purchase: ' . $e->getMessage()]);
        }
    }

    public function createPayment(Request $request)
    {
        if (!Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            DB::beginTransaction();

            $userId = Auth::id();
            $saleId = $request->sale_id;
            $paymentAmount = (float) $request->payment_amount;
            $discount = (float) ($request->discount ?? 0);
            
            $sale = LocalSale::findOrFail($saleId);

            $oldType = $sale->sale_type;
            $oldRemaining = ($oldType === 'estimate') ? 0 : floatval($sale->remaining_amount);

            $totalDeduction = $paymentAmount + $discount;
            $calculatedRemaining = $sale->net_amount - ($sale->advance_amount + $totalDeduction);
            $ledgerDiff = $calculatedRemaining - $oldRemaining;

            // 1. Update Customer/Vendor Ledger closing balance
            if ($sale->party_type === 'customer' && $sale->customer_id) {
                $ledger = CustomerLedger::where('customer_id', $sale->customer_id)
                    ->where('admin_or_user_id', $userId)
                    ->first();
                if ($ledger) {
                    if ($ledgerDiff != 0) {
                        $ledger->increment('closing_balance', $ledgerDiff);
                    }
                } else {
                    CustomerLedger::create([
                        'customer_id' => $sale->customer_id,
                        'admin_or_user_id' => $userId,
                        'closing_balance' => $ledgerDiff,
                    ]);
                }
            } elseif ($sale->party_type === 'vendor' && $sale->vendor_id) {
                $ledger = VendorLedger::where('vendor_id', $sale->vendor_id)
                    ->where('admin_or_user_id', $userId)
                    ->first();
                if ($ledger) {
                    if ($ledgerDiff != 0) {
                        $ledger->decrement('closing_balance', $ledgerDiff);
                    }
                } else {
                    VendorLedger::create([
                        'vendor_id' => $sale->vendor_id,
                        'admin_or_user_id' => $userId,
                        'closing_balance' => -$ledgerDiff,
                    ]);
                }
            }

            // 2. Update Sale status, advance, and remaining
            $sale->sale_type = 'sale';
            $sale->job_status = 'completed';
            $sale->advance_amount = $sale->advance_amount + $paymentAmount;
            $sale->remaining_amount = max(0, $calculatedRemaining);
            $sale->save();

            // 3. Create Journal Voucher Receipt for the cash payment
            $accountId = $request->account_id ?? null;
            if ($paymentAmount > 0) {
                $partyName = $sale->party_name ?? 'Walk-in';
                $jvPartyType = in_array($sale->party_type, ['customer', 'vendor']) ? $sale->party_type : 'other';
                $voucherNo = \App\Models\JournalVoucher::generateVoucherNo('receipt');
                
                \App\Models\JournalVoucher::create([
                    'admin_or_user_id' => $userId,
                    'account_id' => $accountId,
                    'voucher_no' => $voucherNo,
                    'voucher_date' => date('Y-m-d'),
                    'voucher_type' => 'receipt',
                    'party_type' => $jvPartyType,
                    'party_id' => $sale->party_type === 'customer' ? $sale->customer_id : ($sale->party_type === 'vendor' ? $sale->vendor_id : null),
                    'party_name' => $partyName,
                    'account_head' => 'Sale',
                    'debit_amount' => 0,
                    'credit_amount' => $paymentAmount,
                    'payment_method' => 'cash',
                    'reference_type' => 'local_sale',
                    'reference_id' => $sale->id,
                    'narration' => 'Wizard Payment for Sale ID: ' . $sale->id . ($discount > 0 ? " (Discount: $discount)" : ''),
                    'status' => 'approved',
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Payment recorded successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Wizard Payment Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to record payment: ' . $e->getMessage()]);
        }
    }

    public function createQuickSale(Request $request)
    {
        if (!Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            DB::beginTransaction();

            $userId = Auth::id();
            $partyType = $request->party_type ?? 'customer';
            
            $customerId = null;
            $customerName = null;
            $customerPhone = null;
            $customerAddress = null;

            if ($partyType === 'customer') {
                $customer = Customer::findOrFail($request->customer_id);
                $customerId = $customer->id;
            } else {
                $customerName = $request->walkin_name;
                $customerPhone = $request->walkin_phone;
                $customerAddress = $request->walkin_address;
            }
            
            $items = [];
            $rates = [];
            $qtys = [];
            $units = [];
            $amounts = [];
            
            $netAmount = 0;
            
            foreach ($request->products as $prod) {
                $items[] = $prod['item_name'];
                $rates[] = $prod['rate'];
                $qtys[] = $prod['qty'];
                $units[] = $prod['unit'] ?? 'pcs';
                
                $amount = (float)$prod['rate'] * (float)$prod['qty'];
                $amounts[] = $amount;
                $netAmount += $amount;
            }

            $sale = LocalSale::create([
                'admin_or_user_id' => $userId,
                'customer_id' => $customerId,
                'party_type' => $partyType,
                'customer_shopname' => $customerName,
                'customer_phone' => $customerPhone,
                'customer_address' => $customerAddress,
                'sale_type' => 'estimate',
                'invoice_number' => LocalSale::generateSaleInvoiceNo(),
                'sale_date' => date('Y-m-d'),
                
                'item' => json_encode($items),
                'rate' => json_encode($rates),
                'qty' => json_encode($qtys),
                'unit' => json_encode($units),
                'amount' => json_encode($amounts),
                
                'grand_total' => $netAmount,
                'net_amount' => $netAmount,
                'advance_amount' => 0,
                'remaining_amount' => $netAmount,
                'job_status' => 'pending',
            ]);

            DB::commit();
            return response()->json(['success' => true, 'sale' => $sale]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Quick Sale Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
