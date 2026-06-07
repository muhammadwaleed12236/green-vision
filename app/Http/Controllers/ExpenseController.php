<?php

namespace App\Http\Controllers;

use App\Models\AddExpense;
use App\Models\Expense;
use App\Traits\AutoJournalVoucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    use AutoJournalVoucher;
    public function expense()
    {
        if (Auth::id()) {
            $userId = Auth::id();

            $expenses = Expense::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure

            return view('admin_panel.expense.expenses', [
                'expenses' => $expenses,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function store_expense_category(Request $request)
    {

        $request->validate([
            'expense_category' => 'required',
        ]);

        if (Auth::id()) {

            $usertype = Auth()->user()->usertype;
            $userId = Auth::id();

            Expense::create([
                'admin_or_user_id' => $userId,
                'expense_name' => $request->expense_category,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            return redirect()->back()->with('success', 'Expense created successfully');
        } else {
            return redirect()->back();
        }
    }

    public function update(Request $request)
    {

        $request->validate([
            'expense_category' => 'required',
        ]);

        $expense_id = $request->input('expense_id');

        Expense::where('id', $expense_id)->update([
            'expense_name' => $request->expense_category,
        ]);

        return redirect()->back()->with('success', 'Expense updated successfully');
    }

    public function delete_Add_ExpenseBtn($id)
    {
        $expense = Expense::find($id);

        if (! $expense) {
            return response()->json(['error' => 'Expense not found.'], 404);
        }

        $expense->delete();

        return response()->json(['success' => 'Expense deleted successfully.']);
    }

    public function addExpenseScreen()
    {
        if (Auth::id()) {
            $userId = Auth::id();

            // Expense categories fetch karna
            $expenseCategories = Expense::where('admin_or_user_id', $userId)->get();

            // Sare expenses fetch karna including job assignment expenses
            $expenses = AddExpense::with('expense')->where('admin_or_user_id', $userId)->orderBy('expense_date', 'desc')->get();

            return view('admin_panel.expense.add_expenses', [
                'expenseCategories' => $expenseCategories,
                'expenses' => $expenses,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function store_addexpense(Request $request)
    {
        if (Auth::id()) {
            $userId = Auth::id();

            // Get expense_id from expense_name
            $expense = Expense::where('expense_name', $request->expense_category)->first();

            $addExpense = AddExpense::create([
                'admin_or_user_id' => $userId,
                'expense_id' => $expense ? $expense->id : null,
                'amount' => $request->amount,
                'expense_date' => $request->date,
                'description' => ($request->title ?? '') . ' - ' . ($request->description ?? ''),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 🔥 Create Journal Voucher Entry for Expense
            $this->createExpensePaymentJournal(
                $expense ? $expense->id : null,
                $request->expense_category,
                $request->amount,
                $request->date,
                ($request->title ?? '') . ' - ' . ($request->description ?? ''),
                $addExpense->id
            );

            return redirect()->back()->with('success', 'Expense added successfully');
        } else {
            return redirect()->back();
        }
    }

    // ✅ Update Expense Function (Added)
    public function update_addexpense(Request $request)
    {
        $request->validate([
            'expense_id' => 'required|exists:add_expenses,id',
            'expense_category' => 'required',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        // Get expense_id from expense_name
        $expenseCat = Expense::where('expense_name', $request->expense_category)->first();

        $expense = AddExpense::findOrFail($request->expense_id);
        $expense->update([
            'expense_id' => $expenseCat ? $expenseCat->id : $expense->expense_id,
            'amount' => $request->amount,
            'expense_date' => $request->date,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Expense updated successfully.');
    }

    public function delete_add_expense($id)
    {
        $expense = AddExpense::find($id);

        if (! $expense) {
            return response()->json(['error' => 'Expense not found.'], 404);
        }

        // 🔥 NEW: Prevent deletion of job assignment expenses
        if (strpos($expense->description, 'Job Assignment #') !== false) {
            return response()->json(['error' => 'Job assignment expenses cannot be deleted manually. Please delete the job order instead.'], 422);
        }

        $expense->delete();

        return response()->json(['success' => 'Expense deleted successfully.']);
    }
}
