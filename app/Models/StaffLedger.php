<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffLedger extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function salesman()
    {
        return $this->belongsTo(Salesman::class, 'saleman_id');
    }
}