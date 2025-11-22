<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'goods_return_id',
        'product_id',
        'quantity',
        'cost',
        'total_cost',
    ];

    public function goodsReturn()
    {
        return $this->belongsTo(GoodsReturn::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
