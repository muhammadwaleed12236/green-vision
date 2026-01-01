<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffRecovery extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function saleman()
    {
        return $this->belongsTo(Salesman::class, 'saleman_ledger_id');
    }
}
