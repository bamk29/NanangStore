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

    /**
     * Increment usage counts for multiple products in a single query.
     * Uses "INSERT ... ON DUPLICATE KEY UPDATE" syntax.
     *
     * @param array $productIds Array of product IDs
     * @return void
     */
    public static function incrementUsageBulk(array $productIds): void
    {
        if (empty($productIds)) {
            return;
        }

        // Count occurrences of each product ID to increment correctly
        $counts = array_count_values($productIds);
        
        $values = [];
        $ids = [];
        $placeholders = [];
        $now = now()->toDateTimeString();

        foreach ($counts as $productId => $count) {
            // (product_id, usage_count, last_7_days_count, last_30_days_count, created_at, updated_at, last_used_at)
            // We only care about product_id, usage_count, last_used_at, and timestamps for factory
            // Actually, we rely on the DB default/nulls for others or must provide them.
            // Since this model might not exist, we must provide all non-nullable fields.
            
            // To be safe and simple with Eloquent, we can just UPSERT using the model.
            // Eloquent upsert() is available in Laravel 8+.
            $ids[] = $productId;
            $values[] = [
                'product_id' => $productId,
                'usage_count' => $count, // This value will be ADDED in the update section
                'last_used_at' => $now,
                'created_at' => $now,
                'updated_at' => $now
            ];
        }

        // Using Eloquent upsert is cleaner than raw SQL if supported
        // upsert(array $values, array|string $uniqueBy, array|null $update = null)
        
        // However, upsert normally REPLACES values. We want to INCREMENT usage_count.
        // Eloquent upsert doesn't support "incrementing" existing values easily (it sets them).
        
        // So we fallback to raw DB statement for performance and correctness (Incrementing).
        // INSERT INTO product_usages (product_id, usage_count, last_used_at, created_at, updated_at) VALUES ...
        // ON DUPLICATE KEY UPDATE usage_count = usage_count + VALUES(usage_count), last_used_at = VALUES(last_used_at)
        
        $sqlValues = [];
        $bindings = [];
        
        foreach ($counts as $id => $cnt) {
            $sqlValues[] = "(?, ?, ?, ?, ?)";
            $bindings[] = $id;
            $bindings[] = $cnt;
            $bindings[] = $now;
            $bindings[] = $now;
            $bindings[] = $now;
        }
        
        $query = "INSERT INTO product_usages (product_id, usage_count, last_used_at, created_at, updated_at) VALUES " . implode(',', $sqlValues) . " 
                  ON DUPLICATE KEY UPDATE usage_count = usage_count + VALUES(usage_count), last_used_at = VALUES(last_used_at), updated_at = VALUES(updated_at)";
                  
        \Illuminate\Support\Facades\DB::insert($query, $bindings);
    }
}