<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LocalSale extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // Get party name based on party type
    public function getPartyNameAttribute()
    {
        if ($this->party_type === 'vendor' && $this->vendor) {
            return $this->vendor->Party_name;
        } elseif ($this->party_type === 'customer' && $this->customer) {
            return $this->customer->customer_name ?? $this->customer->shop_name;
        } else {
            return $this->customer_shopname ?? 'Walk-in';
        }
    }

    // Get party type display
    public function getPartyTypeDisplayAttribute()
    {
        if ($this->party_type === 'vendor') {
            return 'Vendor';
        } elseif ($this->party_type === 'customer') {
            return 'Customer';
        } else {
            return 'Walk-in';
        }
    }

    // LocalSale Model mein yeh relationship add karo
    public function stockOuts()
    {
        return $this->hasMany(StockOut::class, 'local_sales_id');
    }

    public static function generateSaleInvoiceNo()
    {
        // Define the prefix for the invoice number
        $prefix = 'JOb-no-';

        // Fetch the last invoice number from the database
        $lastInvoice = self::orderBy('id', 'desc')->first();

        // Extract the last number, default to 0 if no previous record exists
        $lastNumber = $lastInvoice ? (int) substr($lastInvoice->invoice_number, strlen($prefix)) : 0;

        // Increment the last number and format it with leading zeros
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

        // Return the new invoice number
        return $prefix.$newNumber;
    }
}
