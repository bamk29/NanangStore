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
        'paid_amount',
        'change_amount',
        'payment_method',
        'transaction_type',
        'notes',
        'status',
        'total_reduction_amount',
        'reduction_notes',
        'discount_for_bakso',
        'discount_for_nanang',

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
        // Format: INV-YYYYMMDDHHMMSS-MS (e.g., INV-20231202123045-123)
        // This ensures uniqueness without database queries and avoids race conditions.
        $date = now()->format('YmdHisv');
        
        return "{$prefix}-{$date}";
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
