<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\City;
use App\Models\Designation;
use App\Models\Salesman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SalesmanController extends Controller
{
    // Salesmen List and Add Salesman
    public function salesmen()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $salesmen = Salesman::where('admin_or_user_id', Auth::id())
                ->with(['city', 'area', 'designationRelation']) // Use the renamed method
                ->get();
            $cities = City::where('admin_or_user_id', $userId)->get(); // Changed to $cities
            $designation = Designation::where('admin_or_user_id', $userId)->get();

            return view('admin_panel.salesmen.add_salesman', compact('salesmen', 'cities', 'designation'));
        } else {
            return redirect()->back();
        }
    }

    public function store_salesman(Request $request)
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $authUser = Auth::user();
            $creatorType = $authUser->usertype;

            $salesman = Salesman::create([
                'admin_or_user_id' => $userId,
                'identify' => $creatorType,
                'name' => $request->name,
                'phone' => $request->phone,
                'designation' => $request->designation,
                'address' => $request->address,
                'salary' => $request->designation === 'labour' ? $request->salary : null,
                'status' => $request->status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 👇 Staff Ledger Create (Customer ki tarah)
            \App\Models\StaffLedger::create([
                'admin_or_user_id' => $userId,
                'saleman_id' => $salesman->id,
                'opening_balance' => $request->salary ?? 0, // Salary ko opening balance bana do
                'previous_balance' => $request->salary ?? 0,
                'closing_balance' => $request->salary ?? 0,
                'created_at' => now(),
            ]);

            if (strtolower($request->designation) === 'saleman') {
                \App\Models\User::create([
                    'user_id' => $salesman->id,
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'usertype' => 'salesman',
                    'identify' => $creatorType,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return redirect()->back()->with('success', 'Salesman added successfully');
        } else {
            return redirect()->back()->with('error', 'Unauthorized');
        }
    }

    public function update_salesman(Request $request)
    {
        $salesman_id = $request->input('salesman_id');
        $salesman = Salesman::find($salesman_id);

        if (! $salesman) {
            return redirect()->back()->with('error', 'Salesman not found!');
        }

        $salesman->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'designation' => $request->designation,
            'address' => $request->address,
            'salary' => $request->designation === 'labour' ? $request->salary : null,
            'status' => $request->status,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Salesman updated successfully');
    }

    public function delete($id)
    {
        $salesman = Salesman::find($id);

        if (! $salesman) {
            return response()->json(['status' => false, 'msg' => 'Not found']);
        }

        $salesman->delete();

        return response()->json(['status' => true, 'msg' => 'Deleted']);
    }

    public function getCities()
    {
        $cities = City::select('id', 'city_name')->get();

        return response()->json($cities);
    }

    public function getAreas(Request $request)
    {
        $areas = Area::where('city_name', $request->city_id)
            ->select('id', 'area_name')
            ->get();

        return response()->json($areas);
    }

    public function fetchdesignation()
    {
        return response()->json(Designation::all());
    }

    public function toggleStatus(Request $request)
    {
        $salesman = Salesman::find($request->salesman_id);
        if ($salesman) {
            $salesman->status = $request->status;
            $salesman->save();

            return response()->json(['success' => 'Status updated successfully!']);
        }

        return response()->json(['error' => 'Salesman not found!'], 404);
    }

    public function designation()
    {
        if (Auth::id()) {
            $designations = Designation::where('admin_or_user_id', Auth::id())->get();

            return view('admin_panel.salesmen.add_designation', compact('designations'));
        } else {
            return redirect()->back();
        }
    }

    public function store_designation(Request $request)
    {
        if (Auth::id()) {
            Designation::create([
                'admin_or_user_id' => Auth::id(),
                'designation' => $request->designation,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->back()->with('success', 'Designation added successfully');
        } else {
            return redirect()->back();
        }
    }

    public function update_designation(Request $request)
    {
        $request->validate([
            'designation_id' => 'required|exists:designations,id',
            'designation' => 'required|string|max:255',
        ]);

        $designation = Designation::findOrFail($request->designation_id);
        $designation->update([
            'designation' => $request->designation,
        ]);

        return back()->with('success', 'Designation updated successfully.');
    }

    public function destroy($id)
    {
        $designation = Designation::find($id);

        if (! $designation) {
            return response()->json(['status' => 'error', 'message' => 'Designation not found!']);
        }

        $designation->delete();

        return response()->json(['status' => 'success', 'message' => 'Designation deleted successfully!']);
    }

    // Staff Ledger Page
    public function staff_ledger()
    {
        if (! Auth::check()) {
            return redirect()->back();
        }

        $authUser = Auth::user();
        $userId = Auth::id();

        if ($authUser->usertype === 'admin') {
            $StaffLedgers = \App\Models\StaffLedger::where('admin_or_user_id', $userId)
                ->with('salesman')
                ->get();
        } else {
            $StaffLedgers = collect();
        }

        $Salesmans = Salesman::where('admin_or_user_id', $userId)
            ->where('designation', 'Saleman')
            ->get();

        return view('admin_panel.salesmen.salemen_ladger', compact('StaffLedgers', 'Salesmans'));
    }

    // Store Staff Recovery
    public function staff_recovery_store(Request $request)
    {
        $ledger = \App\Models\StaffLedger::find($request->ledger_id);

        if (! $ledger) {
            return response()->json(['success' => false, 'message' => 'Ledger not found']);
        }

        $ledger->previous_balance = $ledger->closing_balance;
        $ledger->closing_balance -= $request->amount_paid;
        $ledger->save();

        $userId = Auth::id();

        \App\Models\StaffRecovery::create([
            'admin_or_user_id' => $userId,
            'saleman_ledger_id' => $ledger->id,
            'amount_paid' => $request->amount_paid,
            'date' => $request->date,
            'remarks' => $request->remarks,
        ]);

        return response()->json([
            'success' => true,
            'new_closing_balance' => number_format($ledger->closing_balance, 0),
        ]);
    }

    // Staff Recovery List Page
    public function staff_recovery()
    {
        if (! Auth::check()) {
            return redirect()->back();
        }

        $authUser = Auth::user();
        $userId = Auth::id();

        if ($authUser->usertype === 'admin') {
            $Recoveries = \App\Models\StaffRecovery::where('admin_or_user_id', $userId)
                ->with('saleman')
                ->get();

            $Salesmans = Salesman::where('admin_or_user_id', $userId)
                ->where('designation', 'Saleman')
                ->get();
        } else {
            $Recoveries = collect();
            $Salesmans = collect();
        }

        return view('admin_panel.salesmen.salemen_recovery', compact('Recoveries', 'Salesmans'));
    }

    // Update Staff Recovery
    public function updateStaffRecovery(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'adjust_type' => 'required|in:plus,minus',
            'adjust_amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        $recovery = \App\Models\StaffRecovery::findOrFail($id);
        $ledger = \App\Models\StaffLedger::find($recovery->saleman_ledger_id);

        if (! $ledger) {
            return response()->json(['message' => 'Ledger record not found.'], 404);
        }

        $adjustAmount = $request->adjust_amount;

        if ($request->adjust_type === 'plus') {
            $new_amount_paid = $recovery->amount_paid + $adjustAmount;
            $ledger->closing_balance -= $adjustAmount;
        } else {
            $new_amount_paid = $recovery->amount_paid - $adjustAmount;
            $ledger->closing_balance += $adjustAmount;
        }

        $new_amount_paid = max(0, $new_amount_paid);
        $ledger->closing_balance = max(0, $ledger->closing_balance);

        $ledger->save();

        $recovery->update([
            'amount_paid' => $new_amount_paid,
            'remarks' => $request->remarks,
            'date' => $request->date,
        ]);

        return redirect()->route('staff-recovery')->with('success', 'Staff recovery updated successfully.');
    }
}