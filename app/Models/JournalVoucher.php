<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class JournalVoucher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'admin_or_user_id',
        'voucher_no',
        'voucher_date',
        'voucher_type',
        'party_type',
        'party_id',
        'party_name',
        'account_head',
        'debit_amount',
        'credit_amount',
        'payment_method',
        'bank_name',
        'cheque_no',
        'cheque_date',
        'reference_type',
        'reference_id',
        'narration',
        'remarks',
        'status',
    ];

    protected $casts = [
        'voucher_date' => 'date',
        'cheque_date' => 'date',
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
    ];

    // Generate unique voucher number
    public static function generateVoucherNo($type = 'JV')
    {
        $prefix = match($type) {
            'payment' => 'PV',
            'receipt' => 'RV',
            default => 'JV',
        };

        // Search globally (not per user) and include soft-deleted to avoid duplicates
        $lastVoucher = self::withTrashed()
            ->where('voucher_no', 'like', $prefix . '-%')
            ->orderByRaw("CAST(SUBSTRING(voucher_no, " . (strlen($prefix) + 2) . ") AS UNSIGNED) DESC")
            ->first();

        if ($lastVoucher) {
            $lastNum = (int) substr($lastVoucher->voucher_no, strlen($prefix) + 1);
            $newNum = $lastNum + 1;
        } else {
            $newNum = 1;
        }

        return $prefix . '-' . str_pad($newNum, 5, '0', STR_PAD_LEFT);
    }


    // Relationships
    public function vendor()
    {
        return $this->belongsTo(Distributor::class, 'party_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'party_id');
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class, 'party_id');
    }

    public function staff()
    {
        return $this->belongsTo(Salesman::class, 'party_id');
    }

    // Get party name based on type
    public function getPartyNameAttribute($value)
    {
        if ($value) return $value;

        return match($this->party_type) {
            'vendor' => $this->vendor?->name ?? 'N/A',
            'customer' => $this->customer?->customer_name ?? 'N/A',
            'contractor' => $this->contractor?->name ?? 'N/A',
            'staff' => $this->staff?->name ?? 'N/A',
            default => $value ?? 'N/A',
        };
    }

    // Scopes
    public function scopePayments($query)
    {
        return $query->where('voucher_type', 'payment');
    }

    public function scopeReceipts($query)
    {
        return $query->where('voucher_type', 'receipt');
    }

    public function scopeByParty($query, $type, $id)
    {
        return $query->where('party_type', $type)->where('party_id', $id);
    }
}
