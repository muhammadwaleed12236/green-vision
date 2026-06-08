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
        $filter = $request->filter ?? 'daily';
        $selectedDate = $request->date ?? date('Y-m-d');
        
        $query = CashBook::where('admin_or_user_id', $user->id)
                          ->whereNull('deleted_at');

        $titleDate = '';

        if ($filter == 'weekly') {
            $startOfWeek = \Carbon\Carbon::now()->startOfWeek()->format('Y-m-d');
            $endOfWeek = \Carbon\Carbon::now()->endOfWeek()->format('Y-m-d');
            $query->whereBetween('date', [$startOfWeek, $endOfWeek]);
            $titleDate = "This Week (" . \Carbon\Carbon::parse($startOfWeek)->format('d M') . " - " . \Carbon\Carbon::parse($endOfWeek)->format('d M Y') . ")";
        } elseif ($filter == 'monthly') {
            $startOfMonth = \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d');
            $endOfMonth = \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d');
            $query->whereBetween('date', [$startOfMonth, $endOfMonth]);
            $titleDate = "This Month (" . \Carbon\Carbon::now()->format('F Y') . ")";
        } elseif ($filter == 'yearly') {
            $startOfYear = \Carbon\Carbon::now()->startOfYear()->format('Y-m-d');
            $endOfYear = \Carbon\Carbon::now()->endOfYear()->format('Y-m-d');
            $query->whereBetween('date', [$startOfYear, $endOfYear]);
            $titleDate = "This Year (" . \Carbon\Carbon::now()->format('Y') . ")";
        } else {
            // daily
            $query->whereDate('date', $selectedDate);
            $titleDate = \Carbon\Carbon::parse($selectedDate)->format('d M Y, l');
        }

        $entries = $query->orderBy('date', 'asc')->orderBy('id', 'asc')->get();

        // Opening balance is always 0 for each new day/period
        $openingBalance = 0;
        
        // Calculate running balance for the period starting from 0
        $runningBalance = 0;
        foreach ($entries as $entry) {
            $runningBalance += ($entry->debit - $entry->credit);
            $entry->running_balance = $runningBalance;
        }
        
        $closingBalance = $runningBalance;

        return view('admin_panel.cashbook.index', compact('entries', 'selectedDate', 'openingBalance', 'closingBalance', 'filter', 'titleDate'));
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
