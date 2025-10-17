<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductUsage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'usage_count',
        'last_used_at',
        'last_7_days_count',
        'last_30_days_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    /**
     * A product usage belongs to a product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Increment the usage count for a given product ID.
     * This method is called from the Cart to track popularity.
     *
     * @param int $productId
     * @return void
     */
    public static function incrementUsage(int $productId): void
    {
        // Find the usage record for the product, or create a new one if it doesn't exist.
        $usage = self::firstOrCreate(
            ['product_id' => $productId],
            ['usage_count' => 0] // Initialize with 0 if it's a new record.
        );

        // Increment the main counter and update the last used timestamp.
        $usage->increment('usage_count');
        $usage->last_used_at = now();
        $usage->save();
    }
}