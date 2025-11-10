<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'invoice_number',
        'user_id',
        'customer_id',
        'customer_name',
        'total_amount',
        'discount',
        'discount_note',
        'paid_amount',
        'change_amount',
        'payment_method',
        'transaction_type',
        'notes',
        'status',

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public static function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $lastNumber = self::whereDate('created_at', today())
            ->max('id') ?? 0;
        $number = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$date}-{$number}";
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
