<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone',
        'points',
        'debt',
    ];

    /**
     * Get the transactions for the customer.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function ledgers()
    {
        return $this->hasMany(CustomerLedger::class);
    }

    public function updateDebt($amount, $type, $description, $transactionId = null, $referenceId = null)
    {
        $currentDebt = $this->debt;
        $newDebt = $currentDebt;

        // Ensure amount is positive magnitude
        $amount = abs($amount);

        if ($type === 'increase') {
            $newDebt = $currentDebt + $amount;
        } elseif ($type === 'decrease') {
            $newDebt = max(0, $currentDebt - $amount);
        }
        
        $this->debt = $newDebt;
        $this->save();

        $this->ledgers()->create([
            'transaction_id' => $transactionId,
            'type' => $type,
            'amount' => $amount,
            'balance_before' => $currentDebt,
            'balance_after' => $newDebt,
            'description' => $description,
            'reference_id' => $referenceId,
        ]);
        
        return $newDebt;
    }
}