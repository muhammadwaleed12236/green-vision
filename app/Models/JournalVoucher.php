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

    /**
     * Convert an amount into spelling words.
     * Fallback helper if PHP's NumberFormatter class is not found.
     *
     * @param float|int $amount
     * @return string
     */
    public static function amountInWords($amount)
    {
        $amount = (float) $amount;
        if ($amount == 0) {
            return 'zero';
        }

        if (class_exists(\NumberFormatter::class)) {
            $formatter = \NumberFormatter::create('en', \NumberFormatter::SPELLOUT);
            return $formatter->format($amount);
        }

        $units = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
        $tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];
        $groups = ['', 'thousand', 'million', 'billion', 'trillion'];

        // Split into integer and decimal parts
        $integerPart = (int)floor($amount);
        $decimalPart = (int)round(($amount - $integerPart) * 100);

        $result = [];

        if ($integerPart === 0) {
            $result[] = 'zero';
        } else {
            $num_str = (string)$integerPart;
            $num_len = strlen($num_str);
            $group_count = (int)ceil($num_len / 3);
            
            $padded_num = str_pad($num_str, $group_count * 3, '0', STR_PAD_LEFT);
            
            for ($i = 0; $i < $group_count; $i++) {
                $chunk = substr($padded_num, $i * 3, 3);
                $h = (int)$chunk[0];
                $t = (int)$chunk[1];
                $u = (int)$chunk[2];
                
                $chunk_str = '';
                if ($h > 0) {
                    $chunk_str .= $units[$h] . ' hundred';
                }
                
                if ($t > 0 || $u > 0) {
                    if ($h > 0) {
                        $chunk_str .= ' ';
                    }
                    
                    $val = $t * 10 + $u;
                    if ($val < 20) {
                        $chunk_str .= $units[$val];
                    } else {
                        $chunk_str .= $tens[$t];
                        if ($u > 0) {
                            $chunk_str .= '-' . $units[$u];
                        }
                    }
                }
                
                if ($chunk_str !== '') {
                    $group_name = $groups[$group_count - 1 - $i];
                    $result[] = $chunk_str . ($group_name ? ' ' . $group_name : '');
                }
            }
        }

        $word_representation = implode(' ', $result);
        $word_representation = preg_replace('/\s+/', ' ', $word_representation);
        $word_representation = trim($word_representation);

        if ($decimalPart > 0) {
            $dec_word = '';
            if ($decimalPart < 20) {
                $dec_word = $units[$decimalPart];
            } else {
                $t = (int)($decimalPart / 10);
                $u = $decimalPart % 10;
                $dec_word = $tens[$t];
                if ($u > 0) {
                    $dec_word .= '-' . $units[$u];
                }
            }
            $word_representation .= ' point ' . $dec_word;
        }

        return $word_representation;
    }
}
