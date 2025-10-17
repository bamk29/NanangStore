<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhoneOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'notes',
        'status',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(PhoneOrderItem::class);
    }
}