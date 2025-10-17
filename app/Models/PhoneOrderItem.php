<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhoneOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone_order_id',
        'product_id',
        'quantity',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function phoneOrder()
    {
        return $this->belongsTo(PhoneOrder::class);
    }
}