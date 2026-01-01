<?php

namespace App\Http\Controllers;

use App\Models\Contractor;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\CustomerRecovery;
use App\Models\Distributor;
use App\Models\DistributorLedger;
use App\Models\Purchase;
use App\Models\Recovery;
use App\Models\Salesman;
use App\Models\StaffLedger;
use App\Models\StaffRecovery;
use App\Models\Vendor;
use App\Models\VendorLedger;
use App\Models\VendorPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function vendors_payments()
    {
        $Vendors = Vendor::all(['id', 'Party_name']);

        return view('admin_panel.payments.vendors_payments', compact('Vendors'));
    }

    public function storeVendorPayment(Request $request)
    {
        $request->validate([
            'Vendor_id' => 'required|exists:vendors,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'detail' => 'nullable|string|max:255',
        ]);

        $vendorId = $request->Vendor_id;

        // Get last ledger entry for this vendor
        $latestLedger = VendorLedger::where('vendor_id', $vendorId)->latest()->first();

        $previousBalance = $latestLedger ? $latestLedger->closing_balance : 0;
        $newClosing = $previousBalance - $request->amount;

        if ($latestLedger) {
            $latestLedger->update([
                'closing_balance' => $newClosing,
            ]);
            $ledgerId = $latestLedger->id;
        } else {
            $newLedger = VendorLedger::create([
                'admin_or_user_id' => auth()->id(),
                'vendor_id' => $vendorId,
                'previous_balance' => $previousBalance,
                'closing_balance' => $newClosing,
            ]);
            $ledgerId = $newLedger->id;
        }

        // Save the payment
        VendorPayment::create([
            'admin_or_user_id' => auth()->id(),
            'vendor_id' => $vendorId,
            'amount_paid' => $request->amount,
            'payment_date' => $request->date,
            'description' => $request->detail,
        ]);

        // Redirect to receipt page (like distributor)
        return redirect()->route('Vendor.payment.receipt', [
            'vendor_id' => $vendorId,
            'amount' => $request->amount,
            'date' => $request->date,
        ]);
    }

    public function showVendorPaymentReceipt(Request $request)
    {
        $vendorId = $request->vendor_id;
        $amount = $request->amount;
        $date = $request->date;

        $vendor = Vendor::findOrFail($vendorId);

        $latestLedger = VendorLedger::where('vendor_id', $vendorId)
            ->latest('id')
            ->first();

        $closing_balance = $latestLedger ? $latestLedger->closing_balance : 0;

        return view('admin_panel.payments.vendor_payment_receipt', [
            'vendor' => $vendor,
            'amount' => $amount,
            'date' => $date,
            'closing_balance' => $closing_balance,
        ]);
    }

    public function getVendorBalance($id)
    {
        $balance = VendorLedger::where('vendor_id', $id)->value('closing_balance');

        $purchases = Purchase::where('party_name', $id)
            ->select('purchase_date', 'grand_total')
            ->orderBy('purchase_date', 'desc')
            ->get();

        return response()->json([
            'balance' => $balance ?? 0,
            'purchases' => $purchases,
        ]);
    }

    public function customer_payments()
    {
        if (! Auth::check()) {
            return redirect()->back();
        }

        $authUser = Auth::user();

        // Step 1: Determine owner/admin/distributor ID
        if ($authUser->usertype === 'salesman') {
            $salesman = Salesman::where('name', $authUser->name)->first();

            if (! $salesman) {
                return redirect()->back()->with('error', 'Salesman not found.');
            }

            $ownerId = $salesman->admin_or_user_id;

            // Only the logged-in salesman should be visible
            $Salesmans = collect([$salesman]); // wrap in collection for compatibility in view
        } else {
            $ownerId = $authUser->id;

            // All salesmen created by this owner
            $Salesmans = Salesman::where('admin_or_user_id', $ownerId)
                ->where('designation', 'Saleman')
                ->get();
        }

        // Step 2: Fetch all customers under this owner
        $customers = Customer::where('admin_or_user_id', $ownerId)
            ->get(['id', 'customer_name', 'shop_name', 'area']);

        return view('admin_panel.payments.customers_payments', compact('customers', 'Salesmans'));
    }

    public function getCustomerBalance($id)
    {
        $customer = Customer::find($id); // No need to eager load sales anymore

        if (! $customer) {
            return response()->json(['balance' => 0]);
        }

        $latestLedger = CustomerLedger::where('customer_id', $id)
            ->latest('id')
            ->first();

        $closingBalance = $latestLedger ? $latestLedger->closing_balance : 0;

        return response()->json([
            'balance' => $closingBalance,
        ]);
    }

    public function storeCustomerPayment(Request $request)
    {
        $latestLedger = CustomerLedger::where('customer_id', $request->customer_id)
            ->latest('id')
            ->first();

        if (! $latestLedger) {
            return redirect()->back()->with('error', 'Ledger record not found for this customer.');
        }

        $previous_balance = $latestLedger->closing_balance;
        $new_closing_balance = $previous_balance - $request->amount;

        // Update ledger
        $latestLedger->update([
            'closing_balance' => $new_closing_balance,
        ]);

        // Create recovery
        $recovery = CustomerRecovery::create([
            'admin_or_user_id' => auth()->id(),
            'customer_ledger_id' => $latestLedger->customer_id, // Same as distributor logic
            'amount_paid' => $request->amount,
            'salesman' => $request->salesman,
            'date' => $request->date,
            'remarks' => $request->detail,
        ]);

        return redirect()->route('Customer.payment.receipt', [
            'customer_id' => $request->customer_id,
            'amount' => $request->amount,
            'date' => $request->date,
        ]);
    }

    public function showCustomerPaymentReceipt($customer_id, $amount, Request $request)
    {
        $customer = Customer::findOrFail($customer_id);

        $latestLedger = CustomerLedger::where('customer_id', $customer_id)
            ->latest('id')
            ->first();

        $closing_balance = $latestLedger ? $latestLedger->closing_balance : 0;

        // ✅ Fetch latest recovery
        $recovery = CustomerRecovery::where('customer_ledger_id', $customer_id)
            ->latest('id')
            ->first();

        $amount_paid = $amount;
        $remarks = $recovery ? $recovery->remarks : 'N/A';
        $date = \Carbon\Carbon::parse($request->date)->format('d/m/Y');

        return view('admin_panel.payments.customer_payment_receipt', [
            'customer' => $customer,
            'amount' => $amount,
            'amount_paid' => $amount_paid,
            'closing_balance' => $closing_balance,
            'date' => $date,
            'remarks' => $recovery->remarks,
        ]);
    }

    public function Distributor_payments()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $distributors = Distributor::all(['id', 'Customer']); // using 'Customer' as distributor name
            $Salesmans = Salesman::where('admin_or_user_id', $userId)->where('designation', 'Saleman')->get();

            return view('admin_panel.payments.Distributor_payments', compact('distributors', 'Salesmans'));
        } else {
            return redirect()->back();
        }
    }

    public function getDistributorBalance($id)
    {
        $distributor = Distributor::find($id);

        if (! $distributor) {
            return response()->json(['balance' => 0]);
        }

        $latestLedger = DistributorLedger::where('distributor_id', $id)
            ->latest('id')
            ->first();

        $closingBalance = $latestLedger ? $latestLedger->closing_balance : 0;

        return response()->json([
            'balance' => $closingBalance,
        ]);
    }

    public function storeDistributorPayment(Request $request)
    {
        $latestLedger = DistributorLedger::where('distributor_id', $request->distributor_id)
            ->latest('id')
            ->first();

        if (! $latestLedger) {
            return redirect()->back()->with('error', 'Ledger record not found for this distributor.');
        }

        $previous_balance = $latestLedger->closing_balance;
        $new_closing_balance = $previous_balance - $request->amount;

        // Update ledger
        $latestLedger->update([
            'closing_balance' => $new_closing_balance,
        ]);

        // Create recovery
        $recovery = Recovery::create([
            'admin_or_user_id' => auth()->id(),
            'distributor_ledger_id' => $latestLedger->distributor_id,
            'amount_paid' => $request->amount,
            'salesman' => $request->salesman,
            'date' => $request->date,
            'remarks' => $request->detail,
        ]);

        return redirect()->route('Distributor.payment.receipt', [
            'distributor_id' => $request->distributor_id,
            'amount' => $request->amount,
            'date' => $request->date,
        ]);
    }

    public function showPaymentReceipt(Request $request)
    {
        $distributor_id = $request->distributor_id;
        $amount = $request->amount;
        $date = $request->date;

        $distributor = Distributor::findOrFail($distributor_id);

        $latestLedger = DistributorLedger::where('distributor_id', $distributor_id)
            ->latest('id')
            ->first();

        $closing_balance = $latestLedger ? $latestLedger->closing_balance : 0;

        return view('admin_panel.payments.distributor_payment_receipt', [
            'distributor' => $distributor,
            'amount' => $amount,
            'date' => $date,
            'closing_balance' => $closing_balance,
        ]);
    }

    public function staff_payments()
    {
        if (! Auth::check()) {
            return redirect()->back();
        }

        $userId = Auth::id();

        $staffs = Contractor::where('admin_or_user_id', $userId)->get();

        return view('admin_panel.payments.staff_payments', compact('staffs'));
    }

    public function getStaffBalance($id)
    {
        $staff = Contractor::find($id);

        if (! $staff) {
            return response()->json(['balance' => 0]);
        }

        $latestLedger = StaffLedger::where('saleman_id', $id)
            ->latest('id')
            ->first();

        return response()->json([
            'balance' => $latestLedger ? $latestLedger->closing_balance : 0,
        ]);
    }

    public function storeStaffPayment(Request $request)
    {
        $request->validate([
            'staff_id' => 'required',
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
        ]);

        DB::transaction(function () use ($request) {

            // 🔎 Get latest ledger
            $ledger = StaffLedger::where('saleman_id', $request->staff_id)
                ->latest('id')
                ->first();

            // ✅ First time staff → create ledger
            if (! $ledger) {
                $ledger = StaffLedger::create([
                    'admin_or_user_id' => auth()->id(),
                    'saleman_id' => $request->staff_id,
                    'opening_balance' => 0,
                    'previous_balance' => 0,
                    'closing_balance' => 0,
                ]);
            }

            $previous = $ledger->closing_balance;
            $closing = $previous - $request->amount;

            // 🔄 Update ledger
            $ledger->update([
                'previous_balance' => $previous,
                'closing_balance' => $closing,
            ]);

            // 🧾 Store recovery
            StaffRecovery::create([
                'admin_or_user_id' => auth()->id(),
                'saleman_ledger_id' => $request->staff_id,
                'amount_paid' => $request->amount,
                'date' => $request->date,
                'remarks' => $request->detail,
            ]);
        });

        return redirect()->route('Staff.payment.receipt', [
            'staff_id' => $request->staff_id,
            'amount' => $request->amount,
            'date' => $request->date,
        ]);
    }

    public function showStaffPaymentReceipt($staff_id, $amount, Request $request)
    {
        $staff = Contractor::findOrFail($staff_id);

        $latestLedger = StaffLedger::where('saleman_id', $staff_id)
            ->latest('id')
            ->first();

        $closing_balance = $latestLedger ? $latestLedger->closing_balance : 0;

        $recovery = StaffRecovery::where('saleman_ledger_id', $staff_id)
            ->latest('id')
            ->first();

        return view('admin_panel.payments.staff_payment_receipt', [
            'staff' => $staff,
            'amount_paid' => $amount,
            'closing_balance' => $closing_balance,
            'date' => \Carbon\Carbon::parse($request->date)->format('d/m/Y'),
            'remarks' => $recovery ? $recovery->remarks : 'N/A',
        ]);
    }
}
