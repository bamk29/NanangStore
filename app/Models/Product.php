<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'category_id',
        'supplier_id',
        'stock',
        'box_stock',
        'min_stock',
        'retail_price',
        'wholesale_price',
        'wholesale_min_qty',
        'cost_price',
        'base_unit_id',
        'box_unit_id',
        'unit_price',
        'box_price',
        'units_in_box',
        'unit_cost',
        'box_cost',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function getPriceForQuantity($quantity)
    {
        return $quantity >= $this->wholesale_min_qty ? $this->wholesale_price : $this->retail_price;
    }

    public function usage()
    {
        return $this->hasOne(ProductUsage::class);
    }

    public function baseUnit()
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function boxUnit()
    {
        return $this->belongsTo(Unit::class, 'box_unit_id');
    }

    public function scopeOrderByUsage($query, $period = '7days')
    {
        $column = match ($period) {
            '30days' => 'last_30_days_count',
            'total' => 'total_count',
            default => 'last_7_days_count'
        };

        return $query->leftJoin('product_usages', 'products.id', '=', 'product_usages.product_id')
            ->select('products.*')
            ->orderByRaw("COALESCE(product_usages.{$column}, 0) DESC");
    }
}
