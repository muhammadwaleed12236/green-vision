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
    public function local_sale()
    {
        $userId = Auth::id();

        return view('admin_panel.local_sale.add_sale', [
            'Customers' => Customer::where('admin_or_user_id', $userId)->get(),
            'Vendors' => Vendor::where('admin_or_user_id', $userId)->get(),
        ]);
    }

    public function store_local_sale(Request $request)
    {
        $userId = Auth::id();

        $request->validate([
            'party_type' => 'required|in:customer,vendor,walkin',

            'customer_id' => 'required_if:party_type,customer|nullable',
            'vendor_id' => 'required_if:party_type,vendor|nullable',

            'walkin_name' => 'required_if:party_type,walkin|nullable|string',
            'walkin_phone' => 'required_if:party_type,walkin|nullable|string',
            'walkin_address' => 'required_if:party_type,walkin|nullable|string',

            'item_name' => 'required|array|min:1',
            'amount' => 'required|array|min:1',
            'net_amount' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();

        try {
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
            $items = [];
            $heights = [];
            $widths = [];
            $units = [];
            $qtys = [];
            $manualSqfts = [];
            $rates = [];
            $amounts = [];

            foreach ($filteredIndices as $i) {
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
                'job_status' => 'pending',
                'delivery_date' => $request->delivery_date,
                'notify_days_before' => $request->notify_days_before ?? 2,
            ]);

            // Update Customer Ledger: Previous + Remaining = New Closing
            if ($partyType === 'customer' && $remaining > 0) {
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

            if ($partyType === 'vendor' && $remaining > 0) {
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

        return view('admin_panel.local_sale.all_sale', compact('Sales', 'query'));
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
            $invoice_effect_amount = $sale->remaining_amount; // The amount that hits the ledger

            // 1. Customer
            if ($sale->party_type == 'customer' && $sale->customer) {
                $party->name = $sale->customer->customer_name;
                $party->address = $sale->customer->address;
                $party->phone = $sale->customer->phone_number;
                $party->label = "Customer";
                
                $ledger = \DB::table('customer_ledgers')->where('customer_id', $sale->customer_id)->first();
                $current_ledger_balance = $ledger->closing_balance ?? 0;
                
                // For Customer: We Receive. Logic: Previous + Bill = Closing
                // So: Previous = Closing - Bill
                $previous_balance = $current_ledger_balance - $invoice_effect_amount;

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
                $current_ledger_balance = $ledger->closing_balance ?? 0;

                // For Vendor: We Pay. Logic: Previous - Bill = Closing (Since we sold to them, we owe less)
                // So: Previous = Closing + Bill
                $previous_balance = $current_ledger_balance + $invoice_effect_amount;

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
                $current_ledger_balance = $sale->remaining_amount;
                $previous_balance = 0;

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
        $invoice_effect_amount = $sale->remaining_amount;

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

        $categories = json_decode($sale->category);
        $subcategories = json_decode($sale->subcategory);
        $codes = json_decode($sale->code);
        $items = json_decode($sale->item);
        $sizes = json_decode($sale->size);
        $cartonQtys = json_decode($sale->carton_qty);
        $pcs = json_decode($sale->pcs);

        for ($i = 0; $i < count($codes); $i++) {
            $product = Product::where('item_code', $codes[$i])
                ->where('item_name', $items[$i])
                ->where('category', $categories[$i])
                ->where('sub_category', $subcategories[$i])
                ->where('size', $sizes[$i])
                ->first();

            if ($product) {
                $cartonQty = (int) $cartonQtys[$i];
                $pcsReturned = (int) $pcs[$i];
                $pcsPerCarton = (int) $product->pcs_in_carton;

                $product->carton_quantity += $cartonQty;
                $product->initial_stock += ($cartonQty * $pcsPerCarton) + $pcsReturned;

                $product->save();
            }
        }

        $sale->forceDelete();

        $ledger = CustomerLedger::where('customer_id', $customerId)->latest()->first();
        if ($ledger) {
            $ledger->closing_balance -= $netAmount;
            $ledger->save();
        }

        return redirect()->back()->with('success', 'Local Sale deleted, stock restored, and Customer ledger updated.');
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

        DB::transaction(function () use ($request, $sale) {

            $sale->update([
                'party_type' => $request->party_type,
                'customer_id' => $request->customer_id,
                'vendor_id' => $request->vendor_id,
                'item' => json_encode($request->item ?? []),
                'height' => json_encode($request->height ?? []),
                'width' => json_encode($request->width ?? []),
                'unit' => json_encode($request->unit ?? []),
                'qty' => json_encode($request->qty ?? []),
                'rate' => json_encode($request->rate ?? []),
                'amount' => json_encode($request->amount ?? []),
                'grand_total' => $request->grand_total ?? 0,
                'discount_value' => $request->discount_value ?? 0,
                'advance_amount' => $request->advance_amount ?? 0,
                'net_amount' => $request->net_amount ?? 0,
            ]);

            if ($request->party_type === 'customer') {
                CustomerLedger::updateOrCreate(
                    [
                        'customer_id' => $request->customer_id,
                        'admin_or_user_id' => Auth::id(),
                    ],
                    [
                        'closing_balance' => $request->net_amount,
                    ]
                );
            }
        });

        return redirect()
            ->route('local.sale.invoice', $sale->id)
            ->with('success', 'Sale updated successfully');
    }
}
