<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffRecovery extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function saleman()
    {
        return $this->belongsTo(Salesman::class, 'saleman_ledger_id');
    }
}
