<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_category_id',
        'name',
        'opening_balance',
        'balance_type',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(AccountCategory::class, 'account_category_id');
    }

    public function cashBooks()
    {
        return $this->hasMany(CashBook::class, 'account_id');
    }

    public function journalVouchers()
    {
        return $this->hasMany(JournalVoucher::class, 'account_id');
    }

    public function getCalculatedBalanceAttribute()
    {
        $opening = $this->opening_balance ?? 0;
        
        $cashBookDebits = $this->cashBooks()->sum('debit');
        $cashBookCredits = $this->cashBooks()->sum('credit');
        
        $jvDebits = $this->journalVouchers()->sum('debit_amount');
        $jvCredits = $this->journalVouchers()->sum('credit_amount');
        
        $totalDebits = $cashBookDebits + $jvDebits;
        $totalCredits = $cashBookCredits + $jvCredits;
        
        if ($this->balance_type == 'credit') {
            return $opening + $totalCredits - $totalDebits;
        } else {
            return $opening + $totalDebits - $totalCredits;
        }
    }
}
