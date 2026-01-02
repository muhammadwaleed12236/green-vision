<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contractor extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function recoveries()
    {
        return $this->hasManyThrough(
            ContractorRecovery::class,
            ContractorLedger::class,
            'contractor_id',        // ContractorLedger me foreign key
            'contractor_ledger_id', // ContractorRecovery me foreign key
            'id',                   // Contractor ki primary key
            'id'                    // ContractorLedger ki primary key
        );
    }

    public function ledger()
    {
        return $this->hasOne(ContractorLedger::class, 'contractor_id');
    }
}
