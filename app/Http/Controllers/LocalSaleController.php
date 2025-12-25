<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\LocalSale;
use App\Models\Product;
use App\Models\Salesman;
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
        'vendor_id'   => 'required_if:party_type,vendor|nullable',

        'walkin_name'    => 'required_if:party_type,walkin|nullable|string',
        'walkin_phone'   => 'required_if:party_type,walkin|nullable|string',
        'walkin_address' => 'required_if:party_type,walkin|nullable|string',

        'item_name' => 'required|array|min:1',
        'amount'    => 'required|array|min:1',
        'net_amount'=> 'required|numeric|min:0.01',
    ]);

    DB::beginTransaction();

    try {

        $items   = $request->item_name;
        $amounts = array_map('floatval', $request->amount);

        $grossTotal    = array_sum($amounts);
        $grossDiscount = floatval($request->gross_discount ?? 0);
        $netAmount     = $grossTotal - $grossDiscount;

        $partyType = $request->party_type;
        $advance   = floatval($request->advance_amount ?? 0);
        $remaining = $netAmount - $advance;

        // ✅ WALK-IN = FULL PAYMENT ONLY
        if ($partyType === 'walkin') {
            $advance   = $netAmount;
            $remaining = 0;
        }

        $sale = LocalSale::create([
            'admin_or_user_id' => $userId,
            'invoice_number'   => LocalSale::generateSaleInvoiceNo(),
            'Date'             => now(),

            'party_type' => $partyType,
            'customer_id'=> $partyType === 'customer' ? $request->customer_id : null,
            'vendor_id'  => $partyType === 'vendor'   ? $request->vendor_id   : null,

            'customer_shopname' => $partyType === 'walkin' ? $request->walkin_name : null,
            'customer_phone'    => $partyType === 'walkin' ? $request->walkin_phone : null,
            'customer_address'  => $partyType === 'walkin' ? $request->walkin_address : null,

            'item'   => json_encode($items),
            'height' => json_encode($request->height),
            'width'  => json_encode($request->width),
            'unit'   => json_encode($request->unit),
            'qty'    => json_encode($request->qty),
            'amount' => json_encode($amounts),

            'grand_total'      => $grossTotal,
            'discount_value'   => $grossDiscount,
            'net_amount'       => $netAmount,
            'advance_amount'   => $advance,
            'remaining_amount' => $remaining,
            'job_status'       => 'pending',
        ]);

        // ✅ LEDGER (ONLY REMAINING)
        if ($partyType === 'customer' && $remaining > 0) {
            CustomerLedger::updateOrCreate(
                [
                    'customer_id' => $request->customer_id,
                    'admin_or_user_id' => $userId
                ],
                [
                    'closing_balance' => DB::raw("IFNULL(closing_balance,0) + $remaining")
                ]
            );
        }

        if ($partyType === 'vendor' && $remaining > 0) {
            VendorLedger::updateOrCreate(
                [
                    'vendor_id' => $request->vendor_id,
                    'admin_or_user_id' => $userId
                ],
                [
                    'closing_balance' => DB::raw("IFNULL(closing_balance,0) + $remaining")
                ]
            );
        }

        DB::commit();
        return redirect()->back()->with('success', 'Job Order Saved Successfully');

    } catch (\Throwable $e) {
        DB::rollBack();
        return back()->with('error', $e->getMessage());
    }
}



    public function all_local_sale()
    {
        if (! Auth::check()) {
            return redirect()->back();
        }

        $authUser = Auth::user();
        $userType = $authUser->usertype; // admin / distributor / salesman
        $userIdentify = $authUser->identify; // 'admin' / 'distributor'
        $userName = $authUser->name;

        // Agar user salesman hai
        if ($userType === 'salesman') {
            $Sales = LocalSale::where('Saleman', $userName)
                ->where('identify', $userIdentify) // 👈 yeh line add karo
                ->with('customer')
                ->get();
        } else {
            // admin ya distributor ka apna data
            $Sales = LocalSale::where('admin_or_user_id', $authUser->id)
                ->with('customer')
                ->get();
        }

        return view('admin_panel.local_sale.all_sale', compact('Sales'));
    }

    public function show_local_sale($id)
    {
        if (Auth::id()) {
            $sale = LocalSale::with('customer')->findOrFail($id);

            // dd($sale);
            return view('admin_panel.local_sale.show_sale', compact('sale'));
        } else {
            return redirect()->back();
        }
    }

    public function localsaleInvoice($id)
    {
        $sale = LocalSale::with('customer')->findOrFail($id);

        $customerId = $sale->customer_id;
        $adminId = $sale->admin_or_user_id;

        // Fetch latest ledger entry for this customer
        $customerLedger = CustomerLedger::where('customer_id', $customerId)
            ->where('admin_or_user_id', $adminId)
            ->latest()
            ->first();

        return view('admin_panel.local_sale.invoice', compact('sale', 'customerLedger'));
    }

    public function delete_localsale($id)
    {
        $sale = LocalSale::findOrFail($id);
        $customerId = $sale->customer_id;
        $netAmount = $sale->net_amount;

        // Step 1: Decode product-related arrays
        $categories = json_decode($sale->category);
        $subcategories = json_decode($sale->subcategory);
        $codes = json_decode($sale->code);
        $items = json_decode($sale->item);
        $sizes = json_decode($sale->size);
        $cartonQtys = json_decode($sale->carton_qty);
        $pcs = json_decode($sale->pcs);

        // Step 2: Loop through all products in the sale
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

                // Restore stock as it was reduced during sale
                $product->carton_quantity += $cartonQty;
                $product->initial_stock += ($cartonQty * $pcsPerCarton) + $pcsReturned;

                $product->save();
            }
        }

        // Step 3: Delete the sale
        $sale->forceDelete();

        // Step 4: Update customer ledger
        $ledger = CustomerLedger::where('customer_id', $customerId)->latest()->first();
        if ($ledger) {
            $ledger->closing_balance -= $netAmount;
            $ledger->save();
        }

        return redirect()->back()->with('success', 'Local Sale deleted, stock restored, and Customer ledger updated.');
    }

    public function localsaleEdit($id)
    {
        if (!Auth::id()) return redirect()->back();

        $userId = Auth::id();

        return view('admin_panel.local_sale.edit_sale', [
            'original'  => LocalSale::findOrFail($id),
            'Customers' => Customer::where('admin_or_user_id',$userId)->get(),
            'Vendors'   => Vendor::where('admin_or_user_id',$userId)->get(),
        ]);
    }

    public function localsaleupdate(Request $request, $id)
    {
        if (!Auth::id()) return redirect()->back();

        $sale = LocalSale::findOrFail($id);

        DB::transaction(function() use ($request,$sale){

            $sale->update([
                'party_type'      => $request->party_type,
                'customer_id'     => $request->customer_id,
                'vendor_id'       => $request->vendor_id,
                'item'            => json_encode($request->item ?? []),
                'height'          => json_encode($request->height ?? []),
                'width'           => json_encode($request->width ?? []),
                'unit'            => json_encode($request->unit ?? []),
                'qty'             => json_encode($request->qty ?? []),
                'rate'            => json_encode($request->rate ?? []),
                'amount'          => json_encode($request->amount ?? []),
                'grand_total'     => $request->grand_total ?? 0,
                'discount_value'  => $request->discount_value ?? 0,
                'advance_amount'  => $request->advance_amount ?? 0,
                'net_amount'      => $request->net_amount ?? 0,
            ]);

            if($request->party_type === 'customer'){
                CustomerLedger::updateOrCreate(
                    [
                        'customer_id'=>$request->customer_id,
                        'admin_or_user_id'=>Auth::id()
                    ],
                    [
                        'closing_balance'=>$request->net_amount
                    ]
                );
            }
        });

        return redirect()
            ->route('local.sale.invoice',$sale->id)
            ->with('success','Sale updated successfully');
    }
}
