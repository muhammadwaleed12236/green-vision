<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractorLedger extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function contractor()
    {
        return $this->belongsTo(Contractor::class, 'contractor_id');
    }

    public function recoveries()
    {
        return $this->hasMany(ContractorRecovery::class, 'contractor_ledger_id');
    }
}
