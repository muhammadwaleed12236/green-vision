<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountCategory;
use App\Models\Account;

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
}
