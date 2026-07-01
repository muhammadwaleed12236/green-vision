<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\LocalSale;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\VendorLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LocalSaleController extends Controller
{
    public function local_sale(Request $request)
    {
        $userId = Auth::id();
        $cloneEstimate = null;

        if ($request->filled('clone_from_estimate')) {
            $cloneEstimate = LocalSale::where('admin_or_user_id', $userId)
                ->where('id', $request->clone_from_estimate)
                ->first();
        }

        return view('admin_panel.local_sale.add_sale', [
            'Customers' => Customer::where('admin_or_user_id', $userId)->get(),
            'Vendors' => Vendor::where('admin_or_user_id', $userId)->get(),
            'cloneEstimate' => $cloneEstimate,
        ]);
    }

    public function store_local_sale(Request $request)
    {
        $userId = Auth::id();

        $rules = [
            'item_name' => 'required|array|min:1',
            'amount' => 'required|array|min:1',
            'net_amount' => 'required|numeric|min:0.01',
        ];

        if ($request->sale_type !== 'estimate') {
            $rules['party_type'] = 'required|in:customer,vendor,walkin';
            $rules['customer_id'] = 'required_if:party_type,customer|nullable';
            $rules['vendor_id'] = 'required_if:party_type,vendor|nullable';
            $rules['walkin_name'] = 'required_if:party_type,walkin|nullable|string';
            $rules['walkin_phone'] = 'required_if:party_type,walkin|nullable|string';
            $rules['walkin_address'] = 'required_if:party_type,walkin|nullable|string';
        }

        $request->validate($rules);

        DB::beginTransaction();

        try {
            $rawItemIds = $request->item_id;
            $rawItems = $request->item_name;
            $rawHeights = $request->height;
            $rawWidths = $request->width;
            $rawUnits = $request->unit;
            $rawQtys = $request->qty;
            $rawManualSqft = $request->manual_sqft;
            $rawRates = $request->rate;
            $rawAmounts = $request->amount;

            // Filter out indices where item_name is empty
            $filteredIndices = [];
            foreach ($rawItems as $index => $item) {
                if (!empty($item)) {
                    $filteredIndices[] = $index;
                }
            }

            // Reconstruct arrays based on filtered indices
            $itemIds = [];
            $items = [];
            $heights = [];
            $widths = [];
            $units = [];
            $qtys = [];
            $manualSqfts = [];
            $rates = [];
            $amounts = [];

            foreach ($filteredIndices as $i) {
                $itemIds[] = $rawItemIds[$i] ?? null;
                $items[] = $rawItems[$i];
                $heights[] = $rawHeights[$i] ?? null;
                $widths[] = $rawWidths[$i] ?? null;
                $units[] = $rawUnits[$i] ?? null;
                $qtys[] = $rawQtys[$i] ?? null;
                $manualSqfts[] = $rawManualSqft[$i] ?? null;
                $rates[] = $rawRates[$i] ?? null;
                $amounts[] = floatval($rawAmounts[$i] ?? 0);
            }

            // Recalculate totals based on filtered items
            $grossTotal = array_sum($amounts);
            $grossDiscount = floatval($request->gross_discount ?? 0);
            $netAmount = $grossTotal - $grossDiscount;

            $partyType = $request->party_type;
            $advance = floatval($request->advance_amount ?? 0);
            $remaining = $netAmount - $advance;

            if ($partyType === 'walkin') {
                $advance = $netAmount;
                $remaining = 0;
            }

            $sale = LocalSale::create([
                'admin_or_user_id' => $userId,
                'estimate_id' => $request->estimate_id,
                'sale_type' => $request->sale_type ?? 'estimate',
                'invoice_number' => LocalSale::generateSaleInvoiceNo(),
                'sale_date' => $request->sale_date ?? now(),

                'party_type' => $partyType,
                'customer_id' => $partyType === 'customer' ? $request->customer_id : null,
                'vendor_id' => $partyType === 'vendor' ? $request->vendor_id : null,

                'customer_shopname' => $partyType === 'walkin' ? $request->walkin_name : null,
                'customer_phone' => $partyType === 'walkin' ? $request->walkin_phone : null,
                'customer_address' => $partyType === 'walkin' ? $request->walkin_address : null,

                'item' => json_encode($items),
                'height' => json_encode($heights),
                'width' => json_encode($widths),
                'unit' => json_encode($units),
                'qty' => json_encode($qtys),
                'manual_sqft' => json_encode($manualSqfts),
                'rate' => json_encode($rates), // Added rate also
                'amount' => json_encode($amounts),

                'grand_total' => $grossTotal,
                'discount_value' => $grossDiscount,
                'net_amount' => $netAmount,
                'advance_amount' => $advance,
                'remaining_amount' => $remaining,
                'job_status' => ($request->sale_type === 'sale') ? 'completed' : 'pending',
                'delivery_date' => ($request->sale_type === 'sale') ? null : $request->delivery_date,
                'notify_days_before' => ($request->sale_type === 'sale') ? 0 : ($request->notify_days_before ?? 2),
            ]);

            // Automatically reduce stock if it's a direct Sale
            if ($request->sale_type === 'sale') {
                foreach ($items as $index => $itemName) {
                    $productId = isset($itemIds[$index]) ? intval($itemIds[$index]) : null;
                    if ($productId) {
                        // Pessimistic lock to prevent race conditions on concurrent stock updates
                        $productModel = Product::where('id', $productId)->lockForUpdate()->first();
                        if ($productModel) {
                            $openingStock = floatval($productModel->initial_stock ?? 0);
                            $usedStock = floatval($qtys[$index] ?? 0);
                            $closingStock = max($openingStock - $usedStock, 0);

                            // Create StockOut record
                            \App\Models\StockOut::create([
                                'admin_or_user_id' => $userId,
                                'product_id' => $productId,
                                'local_sales_id' => $sale->id,
                                'current_stock' => $openingStock,    // Opening Stock
                                'close_stock' => $closingStock,      // Closing Stock (Remaining)
                                'total_stock' => $usedStock,         // Used Stock
                                'created_at' => \Carbon\Carbon::now(),
                                'updated_at' => \Carbon\Carbon::now(),
                            ]);

                            // Update product's initial_stock with the closing stock
                            $productModel->initial_stock = $closingStock;
                            $productModel->save();
                        }
                    }
                }
            }

            // Update Customer Ledger: Previous + Remaining = New Closing
            if ($partyType === 'customer' && $remaining > 0 && ($request->sale_type !== 'estimate')) {
                // Fetch or Create Ledger
                $ledger = CustomerLedger::where('customer_id', $request->customer_id)
                    ->where('admin_or_user_id', $userId)
                    ->first();

                if (!$ledger) {
                   $ledger = new CustomerLedger();
                   $ledger->customer_id = $request->customer_id;
                   $ledger->admin_or_user_id = $userId;
                   $ledger->opening_balance = 0;
                   $ledger->previous_balance = 0;
                }
                
                $ledger->closing_balance += $remaining;
                $ledger->save();
            }

            if ($partyType === 'vendor' && $remaining > 0 && ($request->sale_type !== 'estimate')) {
                $ledger = VendorLedger::where('vendor_id', $request->vendor_id)
                    ->where('admin_or_user_id', $userId)
                    ->first();

                 if (!$ledger) {
                   $ledger = new VendorLedger();
                   $ledger->vendor_id = $request->vendor_id;
                   $ledger->admin_or_user_id = $userId;
                   $ledger->opening_balance = 0;
                   $ledger->previous_balance = 0;
                }

                $ledger->closing_balance -= $remaining; // Vendors usually imply payable, logic might differ but keeping existing
                $ledger->save();
            }

            DB::commit();

            if ($sale->sale_type === 'booking') {
                return redirect()->route('job-orders.index', ['booking_id' => $sale->id, 'quick_assign' => 'true'])->with('success', 'Booking Saved Successfully');
            }

            return redirect()->route('show-local-sale', $sale->id)->with('success', 'Job Order Saved Successfully');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }

    public function all_local_sale(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->back();
        }

        $authUser = Auth::user();
        $userType = $authUser->usertype;
        $userIdentify = $authUser->identify;
        $userName = $authUser->name;

        $query = $request->q ?? null;

        $baseQuery = LocalSale::with(['customer', 'vendor']);

        if ($userType === 'salesman') {
            $baseQuery->where('Saleman', $userName)
                      ->where('identify', $userIdentify);
        } else {
            $baseQuery->where('admin_or_user_id', $authUser->id);
        }

        if ($query) {
            $baseQuery->where(function($q) use ($query) {
                $q->where('invoice_number', 'like', "%{$query}%")
                  ->orWhere('customer_shopname', 'like', "%{$query}%")
                  ->orWhere('customer_phone', 'like', "%{$query}%")
                  ->orWhere('item', 'like', "%{$query}%");

                // Search related customer
                $q->orWhereHas('customer', function($qc) use ($query) {
                    $qc->where('customer_name', 'like', "%{$query}%")
                       ->orWhere('shop_name', 'like', "%{$query}%");
                });

                // Search related vendor
                $q->orWhereHas('vendor', function($qv) use ($query) {
                    $qv->where('Party_name', 'like', "%{$query}%");
                });
            });
        }

        $Sales = $baseQuery->orderBy('id', 'desc')->paginate(30)->withQueryString();

        $Customers = Customer::where('admin_or_user_id', $authUser->id)->orderBy('customer_name')->get();
        $Vendors   = Vendor::where('admin_or_user_id', $authUser->id)->orderBy('Party_name')->get();
        $Products  = Product::where('admin_or_user_id', $authUser->id)->orderBy('item_name')->get();

        return view('admin_panel.local_sale.all_sale', compact('Sales', 'query', 'Customers', 'Vendors', 'Products'));
    }

    public function deliveryNotifications()
    {
        if (! Auth::check()) {
            return redirect()->back();
        }

        $userId = Auth::id();

        // Get all orders with delivery dates that are pending or in-progress
        $orders = LocalSale::whereNotNull('delivery_date')
            ->whereNotIn('job_status', ['completed', 'ready'])
            ->where('admin_or_user_id', $userId)
            ->with('customer')
            ->get()
            ->filter(function($sale) {
                $deliveryDate = \Carbon\Carbon::parse($sale->delivery_date);
                $notifyDate = $deliveryDate->subDays($sale->notify_days_before ?? 2);
                // Show orders from notification date onwards
                return \Carbon\Carbon::now()->greaterThanOrEqualTo($notifyDate);
            });

        return view('admin_panel.notifications.delivery_notifications', compact('orders'));
    }

    public function show_local_sale($id)
    {
        if (Auth::check()) {
            $sale = LocalSale::with(['customer', 'vendor'])->findOrFail($id);

            $party = new \stdClass();
            $ledger_info = new \stdClass();
            
            // Defaut Values
            $current_ledger_balance = 0;
            $invoice_effect_amount = ($sale->sale_type === 'estimate') ? 0 : $sale->remaining_amount; // The amount that hits the ledger

            // 1. Customer
            if ($sale->party_type == 'customer' && $sale->customer) {
                $party->name = $sale->customer->customer_name;
                $party->address = $sale->customer->address;
                $party->phone = $sale->customer->phone_number;
                $party->label = "Customer";
                
                $ledger = \DB::table('customer_ledgers')->where('customer_id', $sale->customer_id)->first();
                $actual_ledger_balance = $ledger->closing_balance ?? 0;
                
                if ($sale->sale_type === 'estimate') {
                    $previous_balance = $actual_ledger_balance;
                    $current_ledger_balance = $actual_ledger_balance + $sale->remaining_amount;
                } else {
                    $current_ledger_balance = $actual_ledger_balance;
                    $previous_balance = $current_ledger_balance - $sale->remaining_amount;
                }

                $ledger_info->type = 'receivable'; // We are to receive
                $ledger_info->previous_balance = $previous_balance;
                $ledger_info->current_balance = $current_ledger_balance;
                $ledger_info->label_prev = "Previous Receivable";
                $ledger_info->label_curr = "Total Receivable";
                $ledger_info->operator = '+';
            } 
            // 2. Vendor
            elseif ($sale->party_type == 'vendor' && $sale->vendor) {
                $party->name = $sale->vendor->Party_name;
                $party->address = $sale->vendor->Party_address;
                $party->phone = $sale->vendor->Party_phone;
                $party->label = "Vendor / Dealer";
                
                $ledger = \DB::table('vendor_ledgers')->where('vendor_id', $sale->vendor_id)->first();
                $actual_ledger_balance = $ledger->closing_balance ?? 0;

                // For Vendor: We Pay. Logic: Previous - Bill = Closing (Since we sold to them, we owe less)
                if ($sale->sale_type === 'estimate') {
                    $previous_balance = $actual_ledger_balance;
                    $current_ledger_balance = $actual_ledger_balance - $sale->remaining_amount;
                } else {
                    $current_ledger_balance = $actual_ledger_balance;
                    $previous_balance = $current_ledger_balance + $sale->remaining_amount;
                }

                $ledger_info->type = 'payable'; // We are to pay
                $ledger_info->previous_balance = $previous_balance;
                $ledger_info->current_balance = $current_ledger_balance;
                $ledger_info->label_prev = "Previous Payable";
                $ledger_info->label_curr = "Net Payable";
                $ledger_info->operator = '-';
            } 
            // 3. Walk-in / Others (Treat like Customer)
            else {
                $party->name = $sale->customer_shopname ?? 'Walk-in Customer';
                $party->address = $sale->customer_address ?? 'N/A';
                $party->phone = $sale->customer_phone ?? 'N/A';
                $party->label = "Walk-in Party";
                
                // Walkin doesn't have a stored ledger usually, assumes just this bill
                $previous_balance = 0;
                $current_ledger_balance = $sale->remaining_amount;

                $ledger_info->type = 'receivable';
                $ledger_info->previous_balance = 0;
                $ledger_info->current_balance = $current_ledger_balance;
                $ledger_info->label_prev = "Previous Balance";
                $ledger_info->label_curr = "Balance Due";
                $ledger_info->operator = '+';
            }

            $jobOrders = \App\Models\JobOrder::where('sale_id', $id)->with(['salesman', 'contractor'])->get();

            return view('admin_panel.local_sale.show_sale', compact('sale', 'party', 'ledger_info', 'jobOrders'));
        }
        return redirect()->back();
    }

    public function localsaleInvoice($id)
    {
        $sale = LocalSale::with(['customer', 'vendor'])->findOrFail($id);

        $party = new \stdClass();
        $ledger_info = new \stdClass();
        
        // Default Values
        $current_ledger_balance = 0;
        $invoice_effect_amount = ($sale->sale_type === 'estimate') ? 0 : $sale->remaining_amount;

        // 1. Customer
        if ($sale->party_type == 'customer' && $sale->customer) {
            $party->name = $sale->customer->customer_name;
            $party->business_name = $sale->customer->business_name ?? $sale->customer->customer_name;
            $party->address = $sale->customer->address;
            $party->phone = $sale->customer->phone_number;
            $party->label = "Customer";
            
            $ledger = \DB::table('customer_ledgers')->where('customer_id', $sale->customer_id)->first();
            $current_ledger_balance = $ledger->closing_balance ?? 0;
            
            $previous_balance = $current_ledger_balance - $invoice_effect_amount;

            $ledger_info->type = 'receivable';
            $ledger_info->previous_balance = $previous_balance;
            $ledger_info->current_balance = $current_ledger_balance;
            $ledger_info->label_prev = "Previous Receivable";
            $ledger_info->label_curr = "Total Receivable";
            $ledger_info->operator = '+';
        }
        // 2. Vendor/Contractor
        elseif ($sale->party_type == 'vendor' && $sale->vendor) {
            $party->name = $sale->vendor->business_name ?? $sale->vendor->name;
            $party->business_name = $sale->vendor->business_name;
            $party->address = $sale->vendor->address;
            $party->phone = $sale->vendor->phone_number;
            $party->label = "Vendor";
            
            $ledger = \DB::table('contractor_ledgers')->where('contractor_id', $sale->vendor_id)->first();
            $current_ledger_balance = $ledger->closing_balance ?? 0;
            
            $previous_balance = $current_ledger_balance - $invoice_effect_amount;

            $ledger_info->type = 'payable';
            $ledger_info->previous_balance = $previous_balance;
            $ledger_info->current_balance = $current_ledger_balance;
            $ledger_info->label_prev = "Previous Payable";
            $ledger_info->label_curr = "Total Payable";
            $ledger_info->operator = '+';
        }
        // Default fallback
        else {
            $party->name = "Walk-in Customer";
            $party->business_name = "Walk-in Customer";
            $party->address = "N/A";
            $party->phone = "N/A";
            $party->label = "Customer";

            $ledger_info->previous_balance = 0;
            $ledger_info->current_balance = $sale->remaining_amount;
            $ledger_info->label_prev = "Previous Balance";
            $ledger_info->label_curr = "Total Balance";
            $ledger_info->operator = '+';
        }

        return view('admin_panel.local_sale.invoice', compact('sale', 'party', 'ledger_info'));
    }

    public function delete_localsale($id)
    {
        $sale = LocalSale::findOrFail($id);
        $customerId = $sale->customer_id;
        $netAmount = $sale->net_amount;

        $categories = json_decode($sale->category) ?? [];
        $subcategories = json_decode($sale->subcategory) ?? [];
        $codes = json_decode($sale->code) ?? [];
        $items = json_decode($sale->item) ?? [];
        $sizes = json_decode($sale->size) ?? [];
        $cartonQtys = json_decode($sale->carton_qty) ?? [];
        $pcs = json_decode($sale->pcs) ?? [];

        if ($sale->sale_type === 'sale') {
            $stockOuts = \App\Models\StockOut::where('local_sales_id', $sale->id)->get();
            foreach ($stockOuts as $so) {
                $product = Product::find($so->product_id);
                if ($product) {
                    $product->initial_stock += floatval($so->total_stock);
                    $product->save();
                }
                $so->delete();
            }
        }

        $sale->forceDelete();

        if ($sale->sale_type !== 'estimate') {
            $ledger = CustomerLedger::where('customer_id', $customerId)->latest()->first();
            if ($ledger) {
                $ledger->closing_balance -= $netAmount;
                $ledger->save();
            }
        }

        return redirect()->back()->with('success', 'Local Sale deleted successfully.');
    }

    public function localsaleEdit($id)
    {
        if (! Auth::id()) {
            return redirect()->back();
        }

        $userId = Auth::id();

        return view('admin_panel.local_sale.edit_sale', [
            'original' => LocalSale::findOrFail($id),
            'Customers' => Customer::where('admin_or_user_id', $userId)->get(),
            'Vendors' => Vendor::where('admin_or_user_id', $userId)->get(),
        ]);
    }

    public function localsaleupdate(Request $request, $id)
    {
        if (! Auth::id()) {
            return redirect()->back();
        }

        $sale = LocalSale::findOrFail($id);
        $oldRemaining = floatval($sale->remaining_amount);
        $oldType = $sale->sale_type;
        $newType = $request->sale_type ?? $oldType;
        $userId = Auth::id();

        DB::beginTransaction();

        try {
            $netAmount = floatval($request->net_amount ?? 0);
            $advance = floatval($request->advance_amount ?? 0);
            $remaining = $netAmount - $advance;
            $partyType = $request->party_type;

            if ($partyType === 'walkin') {
                $advance = $netAmount;
                $remaining = 0;
            }

            // Walk-in: store directly on the sale row
            if ($partyType === 'walkin') {
                $sale->customer_shopname = $request->walkin_name;
                $sale->customer_phone    = $request->walkin_phone;
                $sale->customer_address  = $request->walkin_address;
            } else {
                $sale->customer_shopname = null;
                $sale->customer_phone    = null;
                $sale->customer_address  = null;
            }

            // 1. Stock / StockOut adjustments
            // First, if it was a Sale previously, we restore the stock
            if ($oldType === 'sale') {
                $stockOuts = \App\Models\StockOut::where('local_sales_id', $sale->id)->get();
                foreach ($stockOuts as $so) {
                    $product = Product::find($so->product_id);
                    if ($product) {
                        $product->initial_stock += floatval($so->total_stock);
                        $product->save();
                    }
                    $so->delete();
                }
            }

            // Now, if the new type is a Sale, we reduce stock
            $items = $request->item ?? [];
            $qtys = $request->qty ?? [];
            if ($newType === 'sale') {
                foreach ($items as $index => $itemName) {
                    if (!empty($itemName)) {
                        $productModel = Product::where('item_name', $itemName)->first();
                        if ($productModel) {
                            $openingStock = floatval($productModel->initial_stock ?? 0);
                            $usedStock = floatval($qtys[$index] ?? 0);
                            $closingStock = max($openingStock - $usedStock, 0);

                            // Create StockOut record
                            \App\Models\StockOut::create([
                                'admin_or_user_id' => $userId,
                                'product_id' => $productModel->id,
                                'local_sales_id' => $sale->id,
                                'current_stock' => $openingStock,
                                'close_stock' => $closingStock,
                                'total_stock' => $usedStock,
                                'created_at' => \Carbon\Carbon::now(),
                                'updated_at' => \Carbon\Carbon::now(),
                            ]);

                            // Update product's initial_stock
                            $productModel->initial_stock = $closingStock;
                            $productModel->save();
                        }
                    }
                }
            }

            // 2. Ledger Adjustments
            // Calculate unified change diff
            $ledgerDiff = 0;
            if ($oldType !== 'estimate') {
                $ledgerDiff -= $oldRemaining;
            }
            if ($newType !== 'estimate') {
                $ledgerDiff += $remaining;
            }

            if ($ledgerDiff != 0) {
                if ($partyType === 'customer' && $request->customer_id) {
                    $ledger = CustomerLedger::where('customer_id', $request->customer_id)
                        ->where('admin_or_user_id', $userId)
                        ->first();
                    if ($ledger) {
                        $ledger->increment('closing_balance', $ledgerDiff);
                    } else {
                        CustomerLedger::create([
                            'customer_id' => $request->customer_id,
                            'admin_or_user_id' => $userId,
                            'closing_balance' => $ledgerDiff,
                        ]);
                    }
                } elseif ($partyType === 'vendor' && $request->vendor_id) {
                    $ledger = VendorLedger::where('vendor_id', $request->vendor_id)
                        ->where('admin_or_user_id', $userId)
                        ->first();
                    if ($ledger) {
                        $ledger->decrement('closing_balance', $ledgerDiff); // Vendor is payable (opposite)
                    } else {
                        VendorLedger::create([
                            'vendor_id' => $request->vendor_id,
                            'admin_or_user_id' => $userId,
                            'closing_balance' => -$ledgerDiff,
                        ]);
                    }
                }
            }

            // 3. Update the LocalSale model
            $sale->update([
                'party_type' => $request->party_type,
                'customer_id' => $request->customer_id,
                'vendor_id' => $request->vendor_id,
                'item' => json_encode($items),
                'height' => json_encode($request->height ?? []),
                'width' => json_encode($request->width ?? []),
                'unit' => json_encode($request->unit ?? []),
                'qty' => json_encode($request->qty ?? []),
                'rate' => json_encode($request->rate ?? []),
                'amount' => json_encode($request->amount ?? []),
                'grand_total' => $request->grand_total ?? 0,
                'discount_value' => $request->discount_value ?? 0,
                'advance_amount' => $advance,
                'net_amount' => $netAmount,
                'remaining_amount' => $remaining,
                'sale_type' => $newType,
                'sale_date' => $request->sale_date ?? $sale->sale_date,
                'job_status' => ($newType === 'sale') ? 'completed' : (($oldType === 'sale') ? 'pending' : $sale->job_status),
                'delivery_date' => ($newType === 'sale') ? null : ($request->delivery_date ?? $sale->delivery_date),
                'notify_days_before' => ($newType === 'sale') ? 0 : ($request->notify_days_before ?? $sale->notify_days_before ?? 2),
            ]);

            DB::commit();

            return redirect()
                ->route('local.sale.invoice', $sale->id)
                ->with('success', 'Invoice updated successfully');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function convert_localsale($id, $targetType)
    {
        if (!Auth::check()) {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        if (!in_array($targetType, ['booking', 'sale'])) {
            return redirect()->back()->with('error', 'Invalid target type');
        }

        $sale = LocalSale::findOrFail($id);
        $userId = Auth::id();

        if ($sale->sale_type === 'sale') {
            return redirect()->back()->with('error', 'Cannot convert a completed sale');
        }
        if ($sale->sale_type === $targetType) {
            return redirect()->back()->with('error', 'Already in target state');
        }

        DB::beginTransaction();
        try {
            $oldType = $sale->sale_type;
            $remaining = floatval($sale->remaining_amount);
            $partyType = $sale->party_type;

            // 1. If target is booking (conversion: estimate -> booking)
            if ($targetType === 'booking') {
                if ($remaining > 0) {
                    if ($partyType === 'customer' && $sale->customer_id) {
                        $ledger = CustomerLedger::where('customer_id', $sale->customer_id)
                            ->where('admin_or_user_id', $userId)
                            ->first();

                        if (!$ledger) {
                            $ledger = new CustomerLedger();
                            $ledger->customer_id = $sale->customer_id;
                            $ledger->admin_or_user_id = $userId;
                            $ledger->opening_balance = 0;
                            $ledger->previous_balance = 0;
                        }
                        $ledger->closing_balance += $remaining;
                        $ledger->save();
                    } elseif ($partyType === 'vendor' && $sale->vendor_id) {
                        $ledger = VendorLedger::where('vendor_id', $sale->vendor_id)
                            ->where('admin_or_user_id', $userId)
                            ->first();

                        if (!$ledger) {
                            $ledger = new VendorLedger();
                            $ledger->vendor_id = $sale->vendor_id;
                            $ledger->admin_or_user_id = $userId;
                            $ledger->opening_balance = 0;
                            $ledger->previous_balance = 0;
                        }
                        $ledger->closing_balance -= $remaining;
                        $ledger->save();
                    }
                }

                $sale->update([
                    'sale_type' => 'booking'
                ]);
            }

            // 2. If target is sale (conversion: estimate -> sale, or booking -> sale)
            if ($targetType === 'sale') {
                // If it was an Estimate, the ledger was not updated. Update it now!
                if ($oldType === 'estimate') {
                    if ($remaining > 0) {
                        if ($partyType === 'customer' && $sale->customer_id) {
                            $ledger = CustomerLedger::where('customer_id', $sale->customer_id)
                                ->where('admin_or_user_id', $userId)
                                ->first();

                            if (!$ledger) {
                                $ledger = new CustomerLedger();
                                $ledger->customer_id = $sale->customer_id;
                                $ledger->admin_or_user_id = $userId;
                                $ledger->opening_balance = 0;
                                $ledger->previous_balance = 0;
                            }
                            $ledger->closing_balance += $remaining;
                            $ledger->save();
                        } elseif ($partyType === 'vendor' && $sale->vendor_id) {
                            $ledger = VendorLedger::where('vendor_id', $sale->vendor_id)
                                ->where('admin_or_user_id', $userId)
                                ->first();

                            if (!$ledger) {
                                $ledger = new VendorLedger();
                                $ledger->vendor_id = $sale->vendor_id;
                                $ledger->admin_or_user_id = $userId;
                                $ledger->opening_balance = 0;
                                $ledger->previous_balance = 0;
                            }
                            $ledger->closing_balance -= $remaining;
                            $ledger->save();
                        }
                    }
                }

                // Automatically reduce stock and create StockOut records
                $items = json_decode($sale->item, true) ?? [];
                $qtys = json_decode($sale->qty, true) ?? [];

                foreach ($items as $index => $itemName) {
                    if (!empty($itemName)) {
                        $productModel = Product::where('item_name', $itemName)->first();
                        if ($productModel) {
                            $openingStock = floatval($productModel->initial_stock ?? 0);
                            $usedStock = floatval($qtys[$index] ?? 0);
                            $closingStock = max($openingStock - $usedStock, 0);

                            // Create StockOut record
                            \App\Models\StockOut::create([
                                'admin_or_user_id' => $userId,
                                'product_id' => $productModel->id,
                                'local_sales_id' => $sale->id,
                                'current_stock' => $openingStock,
                                'close_stock' => $closingStock,
                                'total_stock' => $usedStock,
                                'created_at' => \Carbon\Carbon::now(),
                                'updated_at' => \Carbon\Carbon::now(),
                            ]);

                            // Update product's initial_stock
                            $productModel->initial_stock = $closingStock;
                            $productModel->save();
                        }
                    }
                }

                $sale->update([
                    'sale_type' => 'sale',
                    'job_status' => 'completed',
                    'delivery_date' => null,
                    'notify_days_before' => 0,
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Sale converted successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function statusUpdateAjax(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'transaction_id' => 'required|exists:local_sales,id',
            'status' => 'required|in:booking,sale'
        ]);

        $userId = Auth::id();
        $targetType = $request->status;

        DB::beginTransaction();
        try {
            // Use lockForUpdate to prevent race conditions on the transaction object
            $sale = LocalSale::where('admin_or_user_id', $userId)
                ->where('id', $request->transaction_id)
                ->lockForUpdate()
                ->firstOrFail();

            $oldType = $sale->sale_type;
            $remaining = floatval($sale->remaining_amount);
            $partyType = $sale->party_type;

            if ($sale->sale_type === 'sale') {
                return response()->json(['success' => false, 'message' => 'Cannot convert a completed sale'], 400);
            }
            if ($sale->sale_type === $targetType) {
                return response()->json(['success' => false, 'message' => 'Already in target state'], 400);
            }

            // 1. Target is booking
            if ($targetType === 'booking') {
                if ($remaining > 0) {
                    if ($partyType === 'customer' && $sale->customer_id) {
                        $ledger = CustomerLedger::where('customer_id', $sale->customer_id)
                            ->where('admin_or_user_id', $userId)
                            ->first();

                        if (!$ledger) {
                            $ledger = new CustomerLedger();
                            $ledger->customer_id = $sale->customer_id;
                            $ledger->admin_or_user_id = $userId;
                            $ledger->opening_balance = 0;
                            $ledger->previous_balance = 0;
                        }
                        $ledger->closing_balance += $remaining;
                        $ledger->save();
                    } elseif ($partyType === 'vendor' && $sale->vendor_id) {
                        $ledger = VendorLedger::where('vendor_id', $sale->vendor_id)
                            ->where('admin_or_user_id', $userId)
                            ->first();

                        if (!$ledger) {
                            $ledger = new VendorLedger();
                            $ledger->vendor_id = $sale->vendor_id;
                            $ledger->admin_or_user_id = $userId;
                            $ledger->opening_balance = 0;
                            $ledger->previous_balance = 0;
                        }
                        $ledger->closing_balance -= $remaining;
                        $ledger->save();
                    }
                }

                $sale->update([
                    'sale_type' => 'booking'
                ]);
            }

            // 2. Target is sale
            if ($targetType === 'sale') {
                if ($oldType === 'estimate') {
                    if ($remaining > 0) {
                        if ($partyType === 'customer' && $sale->customer_id) {
                            $ledger = CustomerLedger::where('customer_id', $sale->customer_id)
                                ->where('admin_or_user_id', $userId)
                                ->first();

                            if (!$ledger) {
                                $ledger = new CustomerLedger();
                                $ledger->customer_id = $sale->customer_id;
                                $ledger->admin_or_user_id = $userId;
                                $ledger->opening_balance = 0;
                                $ledger->previous_balance = 0;
                            }
                            $ledger->closing_balance += $remaining;
                            $ledger->save();
                        } elseif ($partyType === 'vendor' && $sale->vendor_id) {
                            $ledger = VendorLedger::where('vendor_id', $sale->vendor_id)
                                ->where('admin_or_user_id', $userId)
                                ->first();

                            if (!$ledger) {
                                $ledger = new VendorLedger();
                                $ledger->vendor_id = $sale->vendor_id;
                                $ledger->admin_or_user_id = $userId;
                                $ledger->opening_balance = 0;
                                $ledger->previous_balance = 0;
                            }
                            $ledger->closing_balance -= $remaining;
                            $ledger->save();
                        }
                    }
                }

                // Automatically reduce stock and create StockOut records
                $items = json_decode($sale->item, true) ?? [];
                $qtys = json_decode($sale->qty, true) ?? [];

                foreach ($items as $index => $itemName) {
                    if (!empty($itemName)) {
                        // Use lockForUpdate to prevent race conditions on stock decrements
                        $productModel = Product::where('item_name', $itemName)
                            ->lockForUpdate()
                            ->first();

                        if ($productModel) {
                            $openingStock = floatval($productModel->initial_stock ?? 0);
                            $usedStock = floatval($qtys[$index] ?? 0);
                            
                            // Prevent negative stock levels
                            if ($openingStock < $usedStock) {
                                throw new \Exception("Insufficient stock for item: {$itemName}. Available: {$openingStock}, Required: {$usedStock}");
                            }

                            $closingStock = $openingStock - $usedStock;

                            // Create StockOut record
                            \App\Models\StockOut::create([
                                'admin_or_user_id' => $userId,
                                'product_id' => $productModel->id,
                                'local_sales_id' => $sale->id,
                                'current_stock' => $openingStock,
                                'close_stock' => $closingStock,
                                'total_stock' => $usedStock,
                                'created_at' => \Carbon\Carbon::now(),
                                'updated_at' => \Carbon\Carbon::now(),
                            ]);

                            // Update product's initial_stock
                            $productModel->initial_stock = $closingStock;
                            $productModel->save();
                        }
                    }
                }

                $sale->update([
                    'sale_type' => 'sale',
                    'job_status' => 'completed',
                    'delivery_date' => null,
                    'notify_days_before' => 0,
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Status updated successfully', 'sale_type' => $sale->sale_type]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}