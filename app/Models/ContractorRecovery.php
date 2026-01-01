<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractorRecovery extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function contractor()
    {
        return $this->hasOneThrough(
            Contractor::class,
            ContractorLedger::class,
            'id',                    // ContractorLedger table ki primary key
            'id',                    // Contractor table ki primary key
            'contractor_ledger_id',  // ContractorRecovery me foreign key
            'contractor_id'          // ContractorLedger me foreign key
        );
    }

    public function ledger()
    {
        return $this->belongsTo(ContractorLedger::class, 'contractor_ledger_id');
    }
}
