<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stock extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'qty',
        'expiry_date',
        'mgf_date',
        // agar aap aur columns add karenge jaise 'batch_no', 'mfg_date', 'expiry_date'
    ];

    protected $table = 'stocks';

    // Stock belongs to a Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class, 'product_id', 'id');
    }
}
