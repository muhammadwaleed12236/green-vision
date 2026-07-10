<?php

namespace App\Http\Controllers;

use App\Models\Contractor;
use App\Models\ContractorLedger;
use App\Models\ContractorRecovery;
use App\Models\Salesman;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractorController extends Controller
{
    // Show all contractors
    public function index()
    {
        if (! Auth::check()) {
            return redirect()->back();
        }

        $userId = Auth::id();
        $contractors = Contractor::all();

        return view('admin_panel.contractor.contractor', compact('contractors'));
    }

    // Store new contractor
    public function store(Request $request)
    {
        if (! Auth::id()) {
            return redirect()->back();
        }

        $userId = Auth::id();

        $contractor = Contractor::create([
            'admin_or_user_id' => $userId,
            'contractor_name' => $request->contractor_name,
            'phone' => $request->phone_number,
            'address' => $request->address,
            'opening_balance' => $request->opening_balance ?? 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Create Contractor Ledger
        ContractorLedger::create([
            'admin_or_user_id' => $userId,
            'contractor_id' => $contractor->id,
            'opening_balance' => $request->opening_balance ?? 0,
            'previous_balance' => $request->opening_balance ?? 0,
            'closing_balance' => $request->opening_balance ?? 0,
            'created_at' => Carbon::now(),
        ]);

        return redirect()->back()->with('success', 'Contractor created successfully');
    }

    // Get contractor data for editing
    public function getContractorData($id)
    {
        $contractor = Contractor::findOrFail($id);
        $ledger = ContractorLedger::where('contractor_id', $id)->first();

        $response = [
            'id' => $contractor->id,
            'contractor_name' => $contractor->contractor_name,
            'phone_number' => $contractor->phone,
            'address' => $contractor->address,
            'ledger' => $ledger,
        ];

        return response()->json($response);
    }

    // Update contractor
    public function update(Request $request)
    {
        $contractor = Contractor::findOrFail($request->contractor_id);

        $contractor->update([
            'contractor_name' => $request->contractor_name,
            'phone' => $request->phone_number,
            'address' => $request->address,
        ]);

        $ledger = ContractorLedger::where('contractor_id', $request->contractor_id)->first();
        $recapeAmount = $request->recape_opening;
        $recapeType = $request->recape_type;

        if ($ledger) {
            if ($recapeType === 'plus') {
                $ledger->opening_balance += $recapeAmount;
            } elseif ($recapeType === 'minus') {
                $ledger->opening_balance -= $recapeAmount;
            }

            $ledger->previous_balance = $ledger->closing_balance;
            $ledger->closing_balance = $ledger->opening_balance;
            $ledger->save();
        }

        return redirect()->back()->with('success', 'Contractor updated successfully');
    }

    // Delete contractor
    public function destroy($id)
    {
        $contractor = Contractor::find($id);

        if (! $contractor) {
            return response()->json(['status' => 'error', 'message' => 'Contractor not found.'], 404);
        }

        $contractor->delete();

        return response()->json(['status' => 'success', 'message' => 'Contractor deleted successfully.']);
    }

    // Show contractor ledger
    public function contractor_ledger()
    {
        if (! Auth::check()) {
            return redirect()->back();
        }

        $userId = Auth::id();
        $ContractorLedgers = ContractorLedger::with('contractor')
            ->get();

        $Salesmans = Salesman::where('designation', 'Saleman')->get();

        return view('admin_panel.contractor.contractor_ledger', compact('ContractorLedgers', 'Salesmans'));
    }

    // Store contractor recovery
    public function contractor_recovery_store(Request $request)
    {
        try {
            $ledger = ContractorLedger::find($request->ledger_id);

            if (!$ledger) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ledger not found'
                ]);
            }

            $ledger->previous_balance = $ledger->closing_balance;
            $ledger->closing_balance -= $request->amount_paid;
            $ledger->save();

            $userId = Auth::id();

            ContractorRecovery::create([
                'admin_or_user_id' => $userId,
                'contractor_ledger_id' => $ledger->id,
                'amount' => $request->amount_paid,
                'recovery_date' => $request->date,
                'remarks' => $request->remarks,
            ]);

            return response()->json([
                'success' => true,
                'new_closing_balance' => number_format($ledger->closing_balance, 0),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // Show contractor recovery list
    public function contractor_recovery()
    {
        if (! Auth::check()) {
            return redirect()->back();
        }

        $userId = Auth::id();
        $Recoveries = ContractorRecovery::with('contractor')
            ->get();

        $Salesmans = Salesman::where('designation', 'Saleman')->get();

        return view('admin_panel.contractor.contractor_recovery', compact('Recoveries', 'Salesmans'));
    }

    // Update contractor recovery
    public function updateRecovery(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'adjust_type' => 'required|in:plus,minus',
            'adjust_amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        $recovery = ContractorRecovery::findOrFail($id);
        $ledger = ContractorLedger::find($recovery->contractor_ledger_id);

        if (! $ledger) {
            return response()->json(['message' => 'Ledger record not found.'], 404);
        }

        $adjustAmount = $request->adjust_amount;

        if ($request->adjust_type === 'plus') {
            $new_amount = $recovery->amount + $adjustAmount;
            $ledger->closing_balance -= $adjustAmount;
        } else {
            $new_amount = $recovery->amount - $adjustAmount;
            $ledger->closing_balance += $adjustAmount;
        }

        $new_amount = max(0, $new_amount);
        $ledger->closing_balance = max(0, $ledger->closing_balance);

        $ledger->save();

        $recovery->update([
            'amount' => $new_amount,
            'remarks' => $request->remarks,
            'recovery_date' => $request->date,
        ]);

        return redirect()->route('contractor-recovery')->with('success', 'Contractor recovery updated successfully.');
    }
}
