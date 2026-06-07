<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractorLedger extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function contractor()
    {
        return $this->belongsTo(Contractor::class, 'contractor_id')->withTrashed();
    }

    public function recoveries()
    {
        return $this->hasMany(ContractorRecovery::class, 'contractor_ledger_id');
    }
}
