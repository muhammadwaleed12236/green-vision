<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffLedger extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function salesman()
    {
        return $this->belongsTo(Salesman::class, 'staff_id');
    }

    public function staff()
    {
        return $this->belongsTo(Salesman::class, 'staff_id');
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class, 'staff_id');
    }
}
