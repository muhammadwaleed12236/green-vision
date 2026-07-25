<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Salesman extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $table = 'sales_mens'; // Explicitly define the table name

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function designationRelation()
    {
        return $this->belongsTo(Designation::class, 'designation');
    }

    public function attendances()
    {
        return $this->hasMany(StaffAttendence::class, 'staff_id', 'id');
    }

    public function salaryPayments()
    {
        return $this->hasMany(StaffSalaryPayment::class, 'staff_id', 'id');
    }

    public function getSalaryLedgerSummary($targetDate = null)
    {
        $targetDate = $targetDate ? \Carbon\Carbon::parse($targetDate) : \Carbon\Carbon::today();

        if (!$this->joining_date) {
            return [
                'total_earned' => 0,
                'total_paid' => 0,
                'balance' => 0
            ];
        }

        $joinDate = \Carbon\Carbon::parse($this->joining_date);
        
        $endDateToUse = ($this->status == 0 && $this->end_date) 
            ? \Carbon\Carbon::parse($this->end_date) 
            : $targetDate;

        if ($endDateToUse->isBefore($joinDate)) {
            $endDateToUse = $joinDate;
        }
        
        if ($targetDate->isBefore($joinDate)) {
             $daysDiff = 0;
        } else {
             $daysDiff = $joinDate->diffInDays($endDateToUse) + 1; 
        }

        $setSalary = $this->salary ?? 0;
        $payBasis = $this->salary_type ?? 'monthly';

        if (strtolower($payBasis) === 'weekly') {
            $perDaySalary = $setSalary / 7;
        } else {
            $perDaySalary = $setSalary / 30;
        }

        $totalEarned = round($perDaySalary * $daysDiff, 0);
        $totalPaid = $this->salaryPayments()->sum('amount_paid');

        return [
            'total_earned' => $totalEarned,
            'total_paid' => $totalPaid,
            'balance' => $totalEarned - $totalPaid
        ];
    }

}