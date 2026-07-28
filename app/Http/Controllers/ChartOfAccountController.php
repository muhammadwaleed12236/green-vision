<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountCategory;
use App\Models\Account;
use App\Models\CashBook;
use App\Models\JournalVoucher;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChartOfAccountController extends Controller
{
    public function index()
    {
        $categories = AccountCategory::with('accounts')->orderBy('name')->get();
        return view('admin_panel.chart_of_accounts.index', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:account_categories,name',
            'description' => 'nullable|string'
        ]);

        AccountCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => true,
        ]);

        return redirect()->back()->with('success', 'Category added successfully!');
    }

    public function updateCategory(Request $request, $id)
    {
        $category = AccountCategory::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255|unique:account_categories,name,' . $category->id,
            'description' => 'nullable|string'
        ]);

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Category updated successfully!');
    }

    public function storeAccount(Request $request)
    {
        $request->validate([
            'account_category_id' => 'required|exists:account_categories,id',
            'name' => 'required|string|max:255|unique:accounts,name',
            'opening_balance' => 'nullable|numeric|min:0',
            'balance_type' => 'required|in:debit,credit',
        ]);

        Account::create([
            'account_category_id' => $request->account_category_id,
            'name' => $request->name,
            'opening_balance' => $request->opening_balance ?? 0,
            'balance_type' => $request->balance_type,
            'status' => true,
        ]);

        return redirect()->back()->with('success', 'Account added successfully!');
    }

    public function updateAccount(Request $request, $id)
    {
        $account = Account::findOrFail($id);
        
        $request->validate([
            'account_category_id' => 'required|exists:account_categories,id',
            'name' => 'required|string|max:255|unique:accounts,name,' . $account->id,
            'opening_balance' => 'nullable|numeric|min:0',
            'balance_type' => 'required|in:debit,credit',
        ]);

        $account->update([
            'account_category_id' => $request->account_category_id,
            'name' => $request->name,
            'opening_balance' => $request->opening_balance ?? 0,
            'balance_type' => $request->balance_type,
        ]);

        return redirect()->back()->with('success', 'Account updated successfully!');
    }

    public function toggleAccountStatus(Request $request, $id)
    {
        $account = Account::findOrFail($id);
        $account->status = !$account->status;
        $account->save();

        return redirect()->back()->with('success', 'Account status updated successfully!');
    }

    public function ledger(Request $request, $id)
    {
        $account = Account::findOrFail($id);

        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        // Fetch CashBook Entries
        $cashBookQuery = CashBook::where('account_id', $id)
            ->whereNull('deleted_at');
            
        if ($fromDate) $cashBookQuery->whereDate('date', '>=', $fromDate);
        if ($toDate) $cashBookQuery->whereDate('date', '<=', $toDate);

        $cashBooks = $cashBookQuery->get()->map(function ($cb) {
            return [
                'date' => $cb->date->format('Y-m-d'),
                'voucher_no' => $cb->title ?: '-', // Use title as voucher no equivalent
                'description' => $cb->description,
                'party' => '-', // No party in cashbook
                'debit' => $cb->debit,
                'credit' => $cb->credit,
                'type' => 'CashBook'
            ];
        });

        // Fetch Journal Voucher Entries
        $jvQuery = JournalVoucher::where('account_id', $id)
            ->whereNull('deleted_at');
            
        if ($fromDate) $jvQuery->whereDate('voucher_date', '>=', $fromDate);
        if ($toDate) $jvQuery->whereDate('voucher_date', '<=', $toDate);

        $jvs = $jvQuery->get()->map(function ($jv) {
            return [
                'date' => $jv->voucher_date->format('Y-m-d'),
                'voucher_no' => $jv->voucher_no,
                'description' => $jv->narration ?: $jv->remarks,
                'party' => $jv->party_name ?: '-',
                // Swap debit and credit from the Bank's perspective
                'debit' => $jv->credit_amount,
                'credit' => $jv->debit_amount,
                'type' => 'JournalVoucher'
            ];
        });

        // Merge and Sort by Date
        $transactions = $cashBooks->concat($jvs)->sortBy('date')->values();

        return view('admin_panel.chart_of_accounts.ledger', compact('account', 'transactions', 'fromDate', 'toDate'));
    }
}
