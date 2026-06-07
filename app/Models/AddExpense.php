<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class AddExpense extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    // Relationship to Expense category
    public function expense()
    {
        return $this->belongsTo(Expense::class, 'expense_id');
    }

    public function expenseCategory()
    {
        return $this->belongsTo(Expense::class, 'expense_id');
    }
}
