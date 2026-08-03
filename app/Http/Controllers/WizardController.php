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

class WizardController extends Controller
{
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
            $vendorId = (int) $request->vendor_id;
            $vendor = Vendor::findOrFail($vendorId);
            $invoiceNo = Purchase::generateInvoiceNo();
            $purchaseDate = date('Y-m-d');

            $products = $request->products ?? []; // Expecting array of objects from frontend

            if (count($products) === 0) {
                return response()->json(['success' => false, 'message' => 'No products provided for purchase']);
            }

            $item_names = [];
            $rates = [];
            $product_modes = [];
            $pcs = [];
            $discounts = [];
            $amounts = [];
            $pcs_cartons = [];

            $grandTotal = 0;

            foreach ($products as $prod) {
                $item_names[] = $prod['item_name'];
                $rates[] = $prod['rate'];
                $product_modes[] = $prod['unit'] ?? '';
                $pcs[] = $prod['pcs'] ?? 0;
                $discounts[] = 0; // Assuming no discount on wizard purchase for now
                
                $amount = (float)$prod['rate'] * (int)($prod['pcs'] ?? 0); // Simplified amount calc
                $amounts[] = $amount;
                $grandTotal += $amount;
                
                $pcs_cartons[] = 0; // Default or calculate if provided
            }

            // Create Purchase
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
                'grand_total' => $grandTotal,
            ]);

            // Update Vendor Ledger
            $ledger = VendorLedger::where('vendor_id', $vendorId)->latest()->first();
            $openingBalance = $vendor->opening_balance ?? 0;

            $paymentAmount = (float) ($request->payment_amount ?? 0);
            $accountId = $request->account_id ?? null;

            if ($ledger) {
                $previousBalance = $ledger->closing_balance;
                $closingBalance = $previousBalance + $grandTotal - $paymentAmount;

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
                    'closing_balance' => $openingBalance + $grandTotal - $paymentAmount,
                ]);
            }

            if ($paymentAmount > 0) {
                \App\Models\VendorPayment::create([
                    'admin_or_user_id' => $userId,
                    'vendor_id' => $vendorId,
                    'amount' => $paymentAmount,
                    'payment_date' => $purchaseDate,
                    'remarks' => 'Wizard Payment for Purchase ID: ' . $purchase->id,
                ]);

                if ($accountId) {
                    $account = \App\Models\Account::find($accountId);
                    if ($account) {
                        $account->opening_balance -= $paymentAmount;
                        $account->save();
                    }
                }
            }

            // Stock update logic can be added here if needed, 
            // but generally we assume products are immediately sold in this flow.
            
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Purchase created successfully', 'purchase_id' => $purchase->id]);

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

            if ($sale->party_type === 'customer' && $sale->customer_id) {
                $customerId = $sale->customer_id;
                
                // Update Customer Ledger
                $ledger = CustomerLedger::where('customer_id', $customerId)->latest()->first();
                if ($ledger) {
                    $previousBalance = $ledger->closing_balance;
                    // Payment reduces the balance. Discount also reduces the balance.
                    $totalDeduction = $paymentAmount + $discount;
                    $closingBalance = $previousBalance - $totalDeduction;

                    // Update existing ledger
                    $ledger->update([
                        'closing_balance' => $closingBalance,
                    ]);

                    \App\Models\CustomerRecovery::create([
                        'admin_or_user_id' => $userId,
                        'customer_ledger_id' => $customerId, // customer_id is stored in customer_ledger_id
                        'date' => date('Y-m-d'),
                        'amount_paid' => $paymentAmount,
                        'remarks' => 'Wizard Payment for Sale ID: ' . $saleId . ($discount > 0 ? " (Discount: $discount)" : ''),
                    ]);
                    
                }
            }

            // Update Sale status and type
            $sale->sale_type = 'sale';
            $sale->job_status = 'completed';
            $sale->advance_amount = $sale->advance_amount + $paymentAmount;
            $sale->remaining_amount = max(0, $sale->net_amount - $sale->advance_amount);
            $sale->save();

            $accountId = $request->account_id ?? null;
            if ($paymentAmount > 0 && $accountId) {
                $account = \App\Models\Account::find($accountId);
                if ($account) {
                    $account->opening_balance += $paymentAmount;
                    $account->save();
                }
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
