<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceAdjustment extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'old_cost_price',
        'new_cost_price',
        'old_retail_price',
        'new_retail_price',
        'old_wholesale_price',
        'new_wholesale_price',
        'old_wholesale_min_qty',
        'new_wholesale_min_qty',
        'notes',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
