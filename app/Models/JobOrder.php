<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function contractor()
    {
        return $this->belongsTo(Contractor::class, 'staff_id');
    }

    public function salesman()
    {
        return $this->belongsTo(Salesman::class, 'staff_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function sale()
    {
        return $this->belongsTo(LocalSale::class, 'sale_id');
    }
}
