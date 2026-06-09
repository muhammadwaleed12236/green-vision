<?php

namespace App\Http\Controllers;

use App\Models\CashBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashBookController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->back();
        }

        $user = Auth::user();
        $selectedMonth = $request->month ?? date('Y-m');

        // Parse selected month
        $startDate = \Carbon\Carbon::parse($selectedMonth . '-01')->startOfMonth();
        $endDate   = $startDate->copy()->endOfMonth();

        // Get all entries for selected month grouped by day
        $monthlyEntries = CashBook::selectRaw('DATE(date) as entry_date,
                                               SUM(debit) as total_debit,
                                               SUM(credit) as total_credit,
                                               (SUM(debit) - SUM(credit)) as daily_balance')
                            ->where('admin_or_user_id', $user->id)
                            ->whereNull('deleted_at')
                            ->whereBetween('date', [$startDate, $endDate])
                            ->groupBy('entry_date')
                            ->orderBy('entry_date', 'asc')
                            ->get();

        // Calculate running balance across the month
        $runningBalance = 0;
        foreach ($monthlyEntries as $row) {
            $runningBalance += $row->daily_balance;
            $row->running_balance = $runningBalance;
        }

        $openingBalance = 0;
        $closingBalance = $runningBalance;
        $totalDebit     = $monthlyEntries->sum('total_debit');
        $totalCredit    = $monthlyEntries->sum('total_credit');

        return view('admin_panel.cashbook.index', compact(
            'monthlyEntries', 'selectedMonth', 'openingBalance', 'closingBalance', 'totalDebit', 'totalCredit'
        ));
    }

    public function history()
    {
        if (!Auth::check()) {
            return redirect()->back();
        }

        $user = Auth::user();
        
        // Get all entries grouped by date
        $dailyHistory = CashBook::selectRaw('DATE(date) as entry_date, 
                                            SUM(debit) as total_debit, 
                                            SUM(credit) as total_credit,
                                            (SUM(debit) - SUM(credit)) as closing_balance')
                              ->where('admin_or_user_id', $user->id)
                              ->whereNull('deleted_at')
                              ->groupBy('entry_date')
                              ->orderBy('entry_date', 'desc')
                              ->paginate(30);

        return view('admin_panel.cashbook.history', compact('dailyHistory'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'debit' => 'nullable|numeric|min:0',
            'credit' => 'nullable|numeric|min:0',
        ]);

        $user = Auth::user();

        $debit = $request->debit ?? 0;
        $credit = $request->credit ?? 0;

        CashBook::create([
            'admin_or_user_id' => $user->id,
            'date' => $request->date,
            'description' => $request->description,
            'debit' => $debit,
            'credit' => $credit,
            'balance' => 0, // Not used in daily system
        ]);

        return back()->with('success', 'Entry added successfully');
    }

    public function update(Request $request)
    {
        $request->validate([
            'entry_id' => 'required|exists:cash_books,id',
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'debit' => 'nullable|numeric|min:0',
            'credit' => 'nullable|numeric|min:0',
        ]);

        $entry = CashBook::findOrFail($request->entry_id);
        
        $entry->update([
            'date' => $request->date,
            'description' => $request->description,
            'debit' => $request->debit ?? 0,
            'credit' => $request->credit ?? 0,
        ]);

        return back()->with('success', 'Entry updated successfully');
    }

    public function delete($id)
    {
        $entry = CashBook::find($id);

        if (!$entry) {
            return response()->json(['status' => 'error', 'message' => 'Entry not found.']);
        }

        $entry->delete();

        return response()->json(['status' => 'success', 'message' => 'Entry deleted successfully.']);
    }
}