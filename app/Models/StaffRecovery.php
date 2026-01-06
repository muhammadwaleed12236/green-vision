<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffRecovery extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    // Try salesman first, then contractor
    public function salesman()
    {
        return $this->belongsTo(Salesman::class, 'saleman_ledger_id', 'id');
    }

    public function contractor()
    {
        return $this->belongsTo(\App\Models\Contractor::class, 'saleman_ledger_id', 'id');
    }

    // Helper method to get the person name
    public function getPersonNameAttribute()
    {
        return $this->salesman->name ?? $this->contractor->contractor_name ?? 'N/A';
    }
}
