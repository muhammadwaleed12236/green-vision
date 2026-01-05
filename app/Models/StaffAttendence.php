<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffAttendence extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

        public function staff()
    {
        return $this->belongsTo(Salesman::class, 'staff_id', 'id');
    }
}
